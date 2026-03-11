<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Database\ClientInterface;
use Breeze\Entity\CommentEntity;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\SharedEntity;
use Breeze\Entity\StatusEntity;
use Breeze\LikesEnum;
use Breeze\Util\Json;
use Breeze\Util\Parser;
use Breeze\Util\Validate\DataNotFoundException;

class StatusRepository extends BaseRepository implements StatusRepositoryInterface
{
	public const string CACHE_BY_PROFILE = 'getByProfile';
	public const string CACHE_BY_ID = 'getById';

	public function __construct(
		ClientInterface $dbClient,
		protected readonly CommentRepositoryInterface $commentRepository,
		LikeRepositoryInterface $likeRepository
	) {
		parent::__construct($dbClient, $likeRepository);
	}

	public function getTableName(): string
	{
		return StatusEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return StatusEntity::ID;
	}

	public function getColumnPosterId(): string
	{
		return StatusEntity::USER_ID;
	}

	public function getColumns(): array
	{
		return StatusEntity::getColumns();
	}

	/**
	 * @throws InvalidStatusException
	 * @return array [StatusEntity]
	 */
	public function insert(StatusEntity $statusEntity): array
	{
		$this->dbClient->insert(StatusEntity::TABLE, [
			StatusEntity::WALL_ID => 'int',
			StatusEntity::USER_ID => 'int',
			SharedEntity::CREATED_AT => 'int',
			StatusEntity::BODY => 'string',
			StatusEntity::LIKES => 'int',
		], $statusEntity->toInsert(), StatusEntity::ID);

		$newStatusId = $this->dbClient->getInsertedId(StatusEntity::TABLE, StatusEntity::ID);

		if ($newStatusId === 0) {
			throw new InvalidStatusException('error_save_status');
		}
		$this->loadUsersInfo([$statusEntity->getUserId()]);
		$statusEntity->setBody(Parser::bbc($statusEntity->getBody()));
		$statusEntity->setId($newStatusId);
		$statusEntity->setIsNew(true);

		// Invalidate cache for the wall profile
		$this->invalidateProfileCache($statusEntity->getWallId());

		return $this->setLikes($this->setUsers([$statusEntity], [$statusEntity->getUserId()]), LikesEnum::Status);
	}

	/**
	 * @param array $userProfiles [int]
	 * @return array [StatusEntity]
	 */
	public function getByProfile(array $userProfiles = [], int $maxIndex = 0, ?string $cursor = null): array
	{
		$cacheKey = sprintf(
			'%s_%s_cursor_%s_%d',
			self::CACHE_BY_PROFILE,
			implode('_', $userProfiles),
			$cursor ?? 'initial',
			$maxIndex
		);

		$cached = $this->getCache($cacheKey);
		if ($cached !== []) {
			return $cached;
		}

		$result = $this->getBy(StatusEntity::WALL_ID, $userProfiles, $maxIndex, $cursor);
		$this->setCache($cacheKey, $result);

		return $result;
	}

	public function getBy(string $columnName, array $data = [], int $maxIndex = 0, ?string $cursor = null): array
	{
		if (!in_array($columnName, $this->getColumns())) {
			return [];
		}

		$queryParams = array_merge(
			$this->getDefaultQueryParams(),
			[
				'columnName' => $columnName,
				'ids' => $data,
			]
		);

		// Build cursor clause if cursor is provided
		$cursorClause = '';
		if ($cursor !== null) {
			$decodedCursor = $this->decodeCursor($cursor);
			if ($decodedCursor !== null) {
				$cursorClause = '
					AND (
						parent.created_at < {int:cursor_created_at}
						OR (parent.created_at = {int:cursor_created_at} AND parent.id < {int:cursor_id})
					)';
				$queryParams['cursor_created_at'] = $decodedCursor['created_at'];
				$queryParams['cursor_id'] = $decodedCursor['id'];
			}
		}

		$queryParams['limit'] = $maxIndex;

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:from}
			WHERE {raw:columnName} IN ({array_int:ids})
			' . $cursorClause . '
			ORDER BY parent.created_at DESC, parent.id DESC
			LIMIT {int:limit}',
			$queryParams
		);

		$comments = $this->commentRepository->getByProfile($data);

		return $this->prepareData($request, $comments);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function getById(int $id = 0): StatusEntity
	{
		$cacheKey = sprintf('%s_%d', self::CACHE_BY_ID, $id);

		$cached = $this->getCache($cacheKey);
		if (!empty($cached) && $cached instanceof StatusEntity) {
			return $cached;
		}

		$queryParams = array_merge($this->getDefaultQueryParams(), [
			'columnName' => StatusEntity::ID,
			'id' => $id,
		]);

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:from}
			WHERE {raw:columnName} = ({int:id})
			LIMIT 1',
			$queryParams
		);

		if (!$request) {
			throw new DataNotFoundException('error_no_status');
		}

		$comments = $this->commentRepository->getByStatus([$id]);

		$preparedData = $this->prepareData($request, $comments);

		if (!isset($preparedData[$id])) {
			throw new DataNotFoundException('error_no_status');
		}

		$result = $preparedData[$id];
		$this->setCache($cacheKey, $result);

		return $result;
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function getBasicInfoById(int $id): StatusEntity
	{
		$request = $this->dbClient->query(
			'
			SELECT {raw:id}, {raw:wall_id}, {raw:user_id}
			FROM {db_prefix}{raw:table}
			WHERE {raw:id} = {int:status_id}
			LIMIT 1',
			[
				'id' => StatusEntity::ID,
				'wall_id' => StatusEntity::WALL_ID,
				'user_id' => StatusEntity::USER_ID,
				'table' => StatusEntity::TABLE,
				'status_id' => $id,
			]
		);

		if ($this->dbClient->numRows($request) === 0) {
			throw new DataNotFoundException('error_no_status');
		}

		$row = $this->dbClient->fetchAssoc($request);
		$this->dbClient->freeResult($request);

		return StatusEntity::from($row);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function deleteById(int $statusId): bool
	{
		// Get status before deletion to invalidate wall cache
		try {
			$status = $this->getById($statusId);
			$wallId = $status->getWallId();
		} catch (DataNotFoundException $e) {
			$wallId = null;
		}

		$this->commentRepository->deleteByStatusId($statusId);

		if (!$this->delete([$statusId])) {
			throw new DataNotFoundException('error_no_status');
		}

		// Invalidate caches
		$this->setCache(sprintf('%s_%d', self::CACHE_BY_ID, $statusId), null);
		if ($wallId !== null) {
			$this->invalidateProfileCache($wallId);
		}

		return true;
	}

	public function recountComments(): void
	{
		$this->dbClient->query(
			'
			UPDATE {db_prefix}' . StatusEntity::TABLE . ' AS s
			SET comments = (
				SELECT COUNT(*)
				FROM {db_prefix}' . CommentEntity::TABLE . ' AS c
				WHERE c.status_id = s.id
			)',
			[]
		);
	}

	public function recountLikes(): void
	{
		$this->dbClient->query(
			'
			UPDATE {db_prefix}' . StatusEntity::TABLE . ' AS s
			SET likes = (
				SELECT COUNT(*)
				FROM {db_prefix}' . LikeEntity::TABLE . ' AS l
				WHERE l.content_id = s.id AND l.content_type = {string:status_type}
			)',
			[
				'status_type' => LikesEnum::Status->value,
			]
		);
	}

	/**
	 * @param array $status [StatusEntity]
	 * @param array $usersIds [int]
	 * @return array [StatusEntity]
	 */
	protected function setUsers(array $status, array $usersIds = []): array
	{
		$loadedUsers = $this->loadUsersInfo($usersIds);

		array_walk($status, function ($entity) use ($loadedUsers): void {
			$commentsLoadedUsers = [$entity->getUserId()];

			if ($loadedUsers !== []) {
				$entity->setUsersInfo(array_intersect_key($loadedUsers, array_flip($commentsLoadedUsers)));
			}
		});

		return $status;
	}

	protected function setComments(array $status, array $comments): array
	{
		array_walk($status, function ($singleStatus, $statusId) use ($comments): void {
			$singleStatus->setComments(array_filter($comments, function ($comment) use ($statusId): bool {
				return $comment->getStatusId() === $statusId;
			}));
		});

		return $status;
	}

	/**
	 * @param array $comments [CommentEntity]
	 * @return array [StatusEntity]
	 */
	protected function prepareData(object $request, array $comments = []): array
	{
		$status = [];
		$usersIds = [];

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$row[StatusEntity::BODY] = Parser::bbc($row[StatusEntity::BODY]);
			$status[$row[StatusEntity::ID]] = StatusEntity::from($row);
			$usersIds[] = $row[StatusEntity::WALL_ID];
			$usersIds[] = $row[StatusEntity::USER_ID];
		}

		$this->dbClient->freeResult($request);

		return $this->setComments(
			$this->setLikes(
				$this->setUsers($status, $usersIds),
				LikesEnum::Status
			),
			$comments
		);
	}

	/**
	 * Encode a cursor for pagination
	 *
	 * @param int $id The status ID
	 * @param int $createdAt The created_at timestamp
	 * @return string Base64 encoded cursor
	 */
	public function encodeCursor(int $id, int $createdAt): string
	{
		return base64_encode(json_encode([
			'id' => $id,
			'created_at' => $createdAt,
		]));
	}

	/**
	 * Decode a cursor for pagination
	 *
	 * @param string $cursor Base64 encoded cursor
	 * @return array|null Decoded cursor data or null if invalid
	 */
	public function decodeCursor(string $cursor): ?array
	{
		$decoded = base64_decode($cursor, true);
		if ($decoded === false) {
			return null;
		}

		$data = Json::decode($decoded);
		if (!isset($data['id']) || !isset($data['created_at'])) {
			return null;
		}

		return [
			'id' => (int) $data['id'],
			'created_at' => (int) $data['created_at'],
		];
	}

	/**
	 * Generate next cursor from status entities
	 *
	 * @param array $statuses Array of StatusEntity objects
	 * @return string|null Encoded cursor or null if no statuses
	 */
	public function getNextCursor(array $statuses): ?string
	{
		if ($statuses === []) {
			return null;
		}

		$lastStatus = end($statuses);
		if (!$lastStatus instanceof StatusEntity) {
			return null;
		}

		$createdAt = $lastStatus->getCreatedAt();
		if (!$createdAt instanceof \DateTimeImmutable) {
			return null;
		}

		return $this->encodeCursor(
			$lastStatus->getId(),
			$createdAt->getTimestamp()
		);
	}

	/**
	 * Invalidate all cache entries for a specific wall profile
	 */
	protected function invalidateProfileCache(int $wallId): void
	{
		// Invalidate initial page cache
		$this->setCache(sprintf('%s_%d_cursor_initial_0', self::CACHE_BY_PROFILE, $wallId), null);

		// Note: Cursor-based pagination cache entries will naturally expire
		// We only invalidate the initial page as it's the most commonly accessed
	}
}
