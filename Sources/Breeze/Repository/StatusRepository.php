<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Database\ClientInterface;
use Breeze\Entity\StatusEntity;
use Breeze\Entity\StatusHandledEntity;
use Breeze\LikesEnum;
use Breeze\Util\Parser;
use Breeze\Util\Validate\DataNotFoundException;

class StatusRepository extends BaseRepository implements StatusRepositoryInterface
{
	public const string CACHE_BY_PROFILE = 'getByProfile';

	public function __construct(
		ClientInterface $dbClient,
		protected readonly CommentRepositoryInterface $commentRepository,
		protected readonly LikeRepositoryInterface $likeRepository
	) {
		parent::__construct($dbClient);
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
	 * @return array [StatusHandledEntity]
	 */
	public function insert(StatusEntity $statusEntity): array
	{
		$statusEntity->unsetId();
		$statusEntity->setCreatedAt(time());

		$this->dbClient->insert(StatusEntity::TABLE, [
			StatusEntity::WALL_ID => 'int',
			StatusEntity::USER_ID => 'int',
			StatusEntity::CREATED_AT => 'int',
			StatusEntity::BODY => 'string',
			StatusEntity::LIKES => 'int',
		], $statusEntity->toArray(), StatusEntity::ID);

		$newStatusId = $this->dbClient->getInsertedId(StatusEntity::TABLE, StatusEntity::ID);

		if ($newStatusId === 0) {
			throw new InvalidStatusException('error_save_status');
		}

		$this->loadedUsers = $this->loadUsersInfo([$statusEntity->getUserId()]);
		$statusEntity->setBody(Parser::bbc($statusEntity->getBody()));

		$statusEntity->setId($newStatusId);
		$statusHandledEntity = new StatusHandledEntity($statusEntity->toArray());
		$statusHandledEntity = $this->buildHandledStatus([$statusHandledEntity]);
		array_walk($statusHandledEntity, function ($handledStatus): void {
			$handledStatus->setIsNew(true);
		});

		return $this->setUsersAndLikes($statusHandledEntity, [$statusEntity->getUserId()], [$newStatusId]);
	}

	/**
	 * @return array [StatusHandledEntity]
	 */
	public function getByProfile(array $userProfiles = [], int $start = 0, int $maxIndex = 0): array
	{
		$queryParams = array_merge(
			$this->getDefaultQueryParams(),
			[
				'columnName' => StatusEntity::WALL_ID,
			],
			[
				'start' => $start,
				'maxIndex' => $maxIndex,
				'ids' => $userProfiles,
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

		$comments = $this->commentRepository->getByProfile($userProfiles);

		return $this->buildHandledStatus($this->prepareData($request), $comments);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function getById(int $id = 0): StatusHandledEntity
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

		// @TODO why are we getting by profile id instead of by status id?
		$comments = $this->commentRepository->getByProfile([$id]);

		return $this->buildHandledStatus($this->prepareData($request), $comments)[$id];
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
	 * @param array $status [StatusHandledEntity]
	 * @param array $comments [CommentHandledEntity]
	 * @return array [StatusHandledEntity]
	 */
	protected function buildHandledStatus(array $status, array $comments = []): array
	{
		array_walk($status, function ($singleStatus, $statusId) use ($comments): void {

			$singleStatus->setComments(array_filter($comments, function ($comment) use ($statusId) {
				return $comment->getStatusId() === $statusId;
			}));
			$statusLoadedUsers = [$singleStatus->getUserId(), $singleStatus->getWallId()];

			if (!empty($this->loadedUsers)) {
				$singleStatus->setUsersInfo(array_intersect_key($this->loadedUsers, array_flip($statusLoadedUsers)));
			}
			$singleStatus->setBody(Parser::bbc($singleStatus->getBody()));
		});

		return $status;
	}

	/**
	 * @return array [StatusHandledEntity]
	 */
	protected function prepareData(object $request): array
	{
		$status = [];
		$usersIds = [];
		$statusIds = [];

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$statusIds[] = $row[StatusEntity::ID];

			$status[$row[StatusEntity::ID]] = new StatusHandledEntity(array_map(function ($column) {
				return ctype_digit((string) $column) ? ( (int) $column) : $column;
			}, $row));
			$usersIds[] = $row[StatusEntity::WALL_ID];
			$usersIds[] = $row[StatusEntity::USER_ID];
		}


		$this->dbClient->freeResult($request);

		return $this->setUsersAndLikes($status, $usersIds, $statusIds);
	}

	protected function setUsersAndLikes(array $status, array $usersIds, array $statusIds): array
	{
		$this->loadedUsers = $this->loadUsersInfo($usersIds);
		$likesByContent = $this->likeRepository->getByContent(LikesEnum::Status, $statusIds);

		array_walk($status, function ($statusEntity, $id) use ($likesByContent): void {
			$statusEntity->setLikesInfo($likesByContent[$id] ?? []);
		});

		return $status;
	}
}
