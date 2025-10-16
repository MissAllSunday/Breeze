<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Database\ClientInterface;
use Breeze\Entity\SharedEntity;
use Breeze\Entity\StatusEntity;
use Breeze\LikesEnum;
use Breeze\Util\Parser;
use Breeze\Util\Validate\DataNotFoundException;

class StatusRepository extends BaseRepository implements StatusRepositoryInterface
{
	public const string CACHE_BY_PROFILE = 'getByProfile';

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

		return $this->setLikes($this->setUsers([$statusEntity], [$statusEntity->getUserId()]), LikesEnum::Status);
	}

	/**
	 * @param array $userProfiles [int]
	 * @return array [StatusEntity]
	 */
	public function getByProfile(array $userProfiles = [], int $start = 0, int $maxIndex = 0): array
	{
		return $this->getBy(StatusEntity::WALL_ID, $userProfiles, $start, $maxIndex);
	}

	public function getBy(string $columnName, array $data = [], int $start = 0, int $maxIndex = 0): array
	{
		if (!in_array($columnName, $this->getColumns())) {
			return [];
		}

		$queryParams = array_merge(
			$this->getDefaultQueryParams(),
			[
				'columnName' => $columnName,
			],
			[
				'start' => $start,
				'maxIndex' => $maxIndex,
				'ids' => $data,
			]
		);

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:from}
			WHERE {raw:columnName} IN ({array_int:ids})
			LIMIT {int:start}, {int:maxIndex}',
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
		$queryParams = array_merge($this->getDefaultQueryParamsWithLikes(LikesEnum::Status), [
			'columnName' => StatusEntity::ID,
			'id' => $id,
		]);

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:from}
			LEFT JOIN {db_prefix}{raw:likeJoin}
			WHERE {raw:columnName} = ({int:id})
			LIMIT 1',
			$queryParams
		);

		if (!$request) {
			throw new DataNotFoundException('error_no_status');
		}

		$comments = $this->commentRepository->getByStatus([$id]);

		return $this->prepareData($request, $comments)[$id];
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function deleteById(int $statusId): bool
	{
		$this->commentRepository->deleteByStatusId($statusId);

		if (!$this->delete([$statusId])) {
			throw new DataNotFoundException('error_no_status');
		}

		// @todo handle cache clen up via event

		return true;
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

			if (!empty($loadedUsers)) {
				$entity->setUsersInfo(array_intersect_key($loadedUsers, array_flip($commentsLoadedUsers)));
			}
		});

		return $status;
	}

	protected function setComments(array $status, array $comments): array
	{
		array_walk($status, function ($singleStatus, $statusId) use ($comments): void {
			$singleStatus->setComments(array_filter($comments, function ($comment) use ($statusId) {
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
}
