<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\SharedEntity;
use Breeze\Entity\StatusEntity;
use Breeze\LikesEnum;
use Breeze\Util\Parser;
use Breeze\Util\Validate\DataNotFoundException;

class CommentRepository extends BaseRepository implements CommentRepositoryInterface
{
	public const string CACHE_BY_PROFILE = 'getByProfile';
	public const string CACHE_BY_STATUS = 'getByStatus';
	public const string CACHE_BY_ID = 'getById';

	public function getTableName(): string
	{
		return CommentEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return CommentEntity::ID;
	}

	public function getColumnPosterId(): string
	{
		return CommentEntity::USER_ID;
	}

	public function getColumns(): array
	{
		return CommentEntity::getColumns();
	}

	/**
	 * @throws InvalidCommentException
	 * @return array [CommentEntity]
	 */
	public function insert(CommentEntity $commentEntity): array
	{
		$this->dbClient->insert(
			CommentEntity::TABLE,
			[
				CommentEntity::STATUS_ID => 'int',
				CommentEntity::USER_ID => 'int',
				SharedEntity::CREATED_AT => 'int',
				CommentEntity::BODY => 'string',
				CommentEntity::LIKES => 'int',
			],
			$commentEntity->toInsert(),
			CommentEntity::ID
		);

		$newCommentId = $this->dbClient->getInsertedId(
			CommentEntity::TABLE,
			CommentEntity::ID
		);

		if ($newCommentId === 0) {
			throw new InvalidCommentException('error_save_comment');
		}

		$commentEntity->setBody(Parser::bbc($commentEntity->getBody()));
		$commentEntity->setId($newCommentId);

		// Invalidate cache for the status
		$this->invalidateStatusCache($commentEntity->getStatusId());

		return $this->setLikes(
			$this->setUsers([$commentEntity], [$commentEntity->getUserId()]),
			LikesEnum::Comments
		);
	}

	public function getByProfile(array $userProfiles = []): array
	{
		$cacheKey = sprintf('%s_%s', self::CACHE_BY_PROFILE, implode('_', $userProfiles));

		$cached = $this->getCache($cacheKey);

		if ($cached !== []) {
			return $cached;
		}

		$queryParams = array_merge($this->getDefaultQueryParams(), [
			'columnName' => StatusEntity::WALL_ID,
			'profileIds' => $userProfiles,
			'statusTable' => StatusEntity::TABLE,
			'commentTable' => CommentEntity::TABLE,
			'compare' => StatusEntity::TABLE .
				'.' . StatusEntity::ID . ' = ' . self::PARENT_LIKE_IDENTIFIER . '.' . CommentEntity::STATUS_ID,
		]);

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:from}
				JOIN {db_prefix}{raw:statusTable} AS {raw:statusTable} ON {raw:compare}
			WHERE {raw:columnName} IN({array_int:profileIds})',
			$queryParams
		);

		$result = $this->prepareData($request);
		$this->setCache($cacheKey, $result);

		return $result;
	}

	public function getByStatus(array $statusIds = []): array
	{
		$cacheKey = sprintf('%s_%s', self::CACHE_BY_STATUS, implode('_', $statusIds));

		$cached = $this->getCache($cacheKey);
		if ($cached !== []) {
			return $cached;
		}

		$queryParams = array_merge($this->getDefaultQueryParams(), [
			'columnName' => CommentEntity::STATUS_ID,
			'statusIds' => $statusIds,
		]);

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:from}
			WHERE {raw:columnName} IN({array_int:statusIds})',
			$queryParams
		);

		$result = $this->prepareData($request);
		$this->setCache($cacheKey, $result);

		return $result;
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function getById(int $id = 0): CommentEntity
	{
		$cacheKey = sprintf('%s_%d', self::CACHE_BY_ID, $id);

		$cached = $this->getCache($cacheKey);
		if (!empty($cached) && $cached instanceof CommentEntity) {
			return $cached;
		}

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:from}
			WHERE {raw:columnName} = ({int:id})
			LIMIT {int:limit}',
			array_merge($this->getDefaultQueryParams(), [
				'limit' => 1,
				'id' => $id,
				'columnName' => self::PARENT_LIKE_IDENTIFIER . '.' . CommentEntity::ID,
			])
		);

		if (!$request) {
			throw new DataNotFoundException('error_no_comment');
		}

		$comments = $this->prepareData($request);

		$result = array_shift($comments);

		if ($result === null) {
			throw new DataNotFoundException('error_no_comment');
		}

		$this->setCache($cacheKey, $result);

		return $result;
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function deleteById(int $commentId): bool
	{
		// Get comment before deletion to invalidate status cache
		try {
			$comment = $this->getById($commentId);
			$statusId = $comment->getStatusId();
		} catch (DataNotFoundException $e) {
			$statusId = null;
		}

		if (!$this->delete([$commentId])) {
			throw new DataNotFoundException('error_no_comment');
		}

		// Invalidate caches
		$this->setCache(sprintf('%s_%d', self::CACHE_BY_ID, $commentId), null);
		if ($statusId !== null) {
			$this->invalidateStatusCache($statusId);
		}

		return true;
	}

	public function deleteByStatusId(int $statusId): bool
	{
		// Invalidate cache for this status
		$this->invalidateStatusCache($statusId);

		return $this->dbClient->delete(
			CommentEntity::TABLE,
			'WHERE ' . CommentEntity::STATUS_ID . ' ={int:statusId}',
			['statusId' => $statusId]
		);
	}

	public function countOrphans(): int
	{
		$request = $this->dbClient->query(
			'
			SELECT COUNT(*)
			FROM {db_prefix}' . CommentEntity::TABLE . ' AS c
			LEFT JOIN {db_prefix}' . StatusEntity::TABLE . ' AS s ON (s.id = c.status_id)
			WHERE s.id IS NULL',
			[]
		);

		[$count] = $this->dbClient->fetchRow($request);
		$this->dbClient->freeResult($request);

		return (int) $count;
	}

	public function deleteOrphans(): void
	{
		$this->dbClient->query(
			'
			DELETE c
			FROM {db_prefix}' . CommentEntity::TABLE . ' AS c
			LEFT JOIN {db_prefix}' . StatusEntity::TABLE . ' AS s ON (s.id = c.status_id)
			WHERE s.id IS NULL',
			[]
		);
	}

	public function recountLikes(): void
	{
		$this->dbClient->query(
			'
			UPDATE {db_prefix}' . CommentEntity::TABLE . ' AS c
			SET likes = (
				SELECT COUNT(*)
				FROM {db_prefix}' . LikeEntity::TABLE . ' AS l
				WHERE l.content_id = c.id AND l.content_type = {string:comment_type}
			)',
			[
				'comment_type' => LikesEnum::Comments->value,
			]
		);
	}

	protected function prepareData($request): array
	{
		$comments = [];
		$usersIds = [];

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$row[CommentEntity::BODY] = Parser::bbc($row[CommentEntity::BODY]);
			$comments[$row[CommentEntity::ID]] = CommentEntity::from($row);
			$usersIds[] = (int) $row[CommentEntity::USER_ID];
		}

		$this->dbClient->freeResult($request);

		return $this->setLikes($this->setUsers($comments, $usersIds), LikesEnum::Comments);
	}

	/**
	 * @param array $comments [CommentEntity]
	 * @param array $usersIds [int]
	 * @return array [CommentEntity]
	 */
	protected function setUsers(array $comments, array $usersIds): array
	{
		// Return early if there is nothing to work on
		if ($comments === [] || $usersIds === []) {
			return [];
		}

		$loadedUsers = $this->loadUsersInfo($usersIds);

		/** @var CommentEntity[] $comments */
		array_walk($comments, function ($comment, $id) use ($loadedUsers): void {
			$commentsLoadedUsers = [$comment->getUserId()];

			if ($loadedUsers !== []) {
				$comment->setUsersInfo(array_intersect_key($loadedUsers, array_flip($commentsLoadedUsers)));
			}
		});

		return $comments;
	}

	/**
	 * Invalidate all cache entries for a specific status
	 */
	protected function invalidateStatusCache(int $statusId): void
	{
		// Invalidate cache for this specific status
		$this->setCache(sprintf('%s_%d', self::CACHE_BY_STATUS, $statusId), null);
	}
}
