<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Breeze;
use Breeze\Database\ClientInterface;
use Breeze\Entity\HandledEntityInterface;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\LikeHandledEntity;
use Breeze\LikesEnum;
use Breeze\PermissionsEnum;
use Breeze\Traits\PermissionsTrait;
use Breeze\Traits\TimeTrait;
use DateMalformedStringException;

class LikeRepository extends BaseRepository implements LikeRepositoryInterface
{
 use PermissionsTrait;
 use TimeTrait;

	public function __construct(
		ClientInterface $dbClient
	) {
		parent::__construct($dbClient);
	}

	public function getTableName(): string
	{
		return LikeEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return LikeEntity::COLUMN_ID;
	}

	public function getColumnPosterId(): string
	{
		return LikeEntity::COLUMN_ID_MEMBER;
	}

	public function getColumns(): array
	{
		return LikeEntity::getColumns();
	}

	/**
	 * @return array [LikeHandledEntity]
	 */
	public function getByContent(LikesEnum $type, array $contentIds): array
	{
		$likes = [];

		$request = $this->dbClient->query(
			'
			SELECT ' . implode(', ', LikeEntity::getColumns()) . '
			FROM {db_prefix}' . LikeEntity::TABLE . '
			WHERE ' . LikeEntity::COLUMN_TYPE . ' = {string:type}
				AND ' . LikeEntity::COLUMN_ID . ' IN({array_int:contentIds})',
			[
				'contentIds' => array_map('intval', $contentIds),
				'type' => $type->value,
			]
		);

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$likes[$row[LikeEntity::COLUMN_ID]][$row[LikeEntity::COLUMN_ID_MEMBER]] = $this->buildLikeData($row);
		}
		$this->dbClient->freeResult($request);

		return array_map(function ($likeData) {
			return $this->postBuildLikeData($likeData, count($likeData));
		}, $likes);
	}

	/**
	 * @throws DateMalformedStringException
	 */
	public function getLikeInfo(string $type, int $contentId): array
	{
		$likeInfo = [];
		$likes = $this->getByContent($type, $contentId);
		$usersInfo = $this->loadUsersInfo(array_column($likes, LikeEntity::COLUMN_ID_MEMBER));

		foreach ($likes as $key => $like) {
			$likeInfo[$key]['profile'] = $usersInfo[$like[LikeEntity::COLUMN_ID_MEMBER]];
			$likeInfo[$key]['timestamp'] = timeFormat($like->getLikeTime()->getTimestamp());
		}

		return $likeInfo;
	}

	public function isContentAlreadyLiked(LikeEntity $likeEntity): bool
	{
		$request = $this->dbClient->query(
			'
			SELECT ' . LikeEntity::COLUMN_ID . '
			FROM {db_prefix}' . LikeEntity::TABLE . '
			WHERE ' . LikeEntity::COLUMN_ID_MEMBER . ' = {int:userId}
				AND ' . LikeEntity::COLUMN_TYPE . ' = {string:type}
				AND ' . LikeEntity::COLUMN_ID . ' = {int:contentId}',
			[
				'userId' => $likeEntity->getIdMember(),
				'type' => $likeEntity->getContentType(),
				'contentId' => $likeEntity->getContentId(),
			]
		);

		$numRows = $this->dbClient->numRows($request);

		$this->dbClient->freeResult($request);

		return $numRows > 0;
	}

	/**
	 * @throws InvalidLikeException
	 */
	public function deleteByContent(LikeEntity $likeEntity): void
	{
		$wasDeleted = $this->dbClient->delete(
			LikeEntity::TABLE,
			'WHERE ' . LikeEntity::COLUMN_ID . ' = {int:contentId}
				AND ' . LikeEntity::COLUMN_TYPE . ' = {string:type}
				AND ' . LikeEntity::COLUMN_ID_MEMBER . ' = {int:userId}',
			[
				'contentId' => $likeEntity->getContentId(),
				'type' => $likeEntity->getContentType(),
				'userId' => $likeEntity->getIdMember(),
			]
		);

		if (!$wasDeleted) {
			throw new InvalidLikeException('error_no_like');
		}
	}

	public function insert(LikeEntity $likeEntity): LikeHandledEntity
	{
		$likeEntity->setLikeTime(time());

		$this->dbClient->insert(LikeEntity::TABLE, [
			LikeEntity::COLUMN_ID => 'int',
			LikeEntity::COLUMN_TYPE => 'string',
			LikeEntity::COLUMN_ID_MEMBER => 'int',
			LikeEntity::COLUMN_TIME => 'int',
		], $likeEntity->toArray(), [LikeEntity::COLUMN_ID, LikeEntity::COLUMN_TYPE, LikeEntity::COLUMN_ID_MEMBER]);

		return $this->buildLikeData($likeEntity);
	}

	public function count(LikeEntity $likeEntity): int
	{
		$result = $this->dbClient->query(
			'
			SELECT {int:contentId}
			FROM {db_prefix}' . LikeEntity::TABLE . '
				WHERE ' . LikeEntity::COLUMN_ID . ' = {int:contentId}
				AND ' . LikeEntity::COLUMN_TYPE . ' = {string:type}',
			[
				'contentId' => $likeEntity->getContentId(),
				'type' => $likeEntity->getContentType(),
			]
		);

		$rowCount = $this->dbClient->numRows($result);

		$this->dbClient->freeResult($result);

		return $rowCount;
	}

	/**
	 * @param array $items [HandledEntityInterface]
	 *
	 * @return array [HandledEntityInterface]
	 */
	public function appendLikeData(array $items, string $itemIdName): array
	{
		return array_map(function ($item) use ($itemIdName): HandledEntityInterface {
			$item->setLikesInfo($this->buildLikeData([
				LikeEntity::COLUMN_TYPE => $item[LikeEntity::IDENTIFIER . LikeEntity::COLUMN_TYPE],
				LikeEntity::COLUMN_ID => $item[$itemIdName],
				LikeEntity::COLUMN_ID_MEMBER => $item[LikeEntity::IDENTIFIER . LikeEntity::COLUMN_ID_MEMBER],
			]));

			return $item;
		}, $items);
	}

	public function buildLikeData(
		array $likeData
	): LikeHandledEntity {

		$likeHandledEntity = new LikeHandledEntity($likeData);
		$likeHandledEntity->setCanLike($this->isAllowedTo(PermissionsEnum::LIKES_LIKE));

		return $likeHandledEntity;
	}

	/**
	 * @param array $likeData [LikeHandledEntity]
	 * @return array [LikeHandledEntity]
	 */
	public function postBuildLikeData(array $likeData, int $likesCount): array
	{
		$alreadyLiked = $likesCount > 0;
		$likesTextCount = $likesCount;

		array_map(function (LikeHandledEntity $likeHandledEntity) use ($likesCount, $alreadyLiked, $likesTextCount): void {
			$base = LikeEntity::IDENTIFIER;
			if ($alreadyLiked) {
				$base = 'you_' . $base;
				$likesTextCount = $likesCount - 1;
			}

			$base .= ($this->getText($base . $likesTextCount) !== '') ? $likesTextCount : 'n';

			$likeHandledEntity->setCount($likesCount);
			$likeHandledEntity->setAdditionalInfo([
				'text' => sprintf(
					$this->getText($base),
					$this->commaFormat((string) $likesTextCount)
				),
				'href' => $this->parserText(
					'{scriptUrl}?action=likes;sa=view;ltype={ltype};like={likeId}',
					[
						'ltype' => $likeHandledEntity->getContentType(),
						'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
						'likeId' => $likeHandledEntity->getContentId(),
					]
				),
			]);
		}, $likeData);

		return $likeData;
	}

	// @return array [LikeHandledEntity] with id_member as key
	public function buildLikeDataFromRequest(array $rows): array
	{
		$likesHandled = [];

		foreach ($rows as $row) {
			// Left join often fills up with null values, so we need to skip them, probably should add a where clause in the query itself...
			if (!isset($row[LikeEntity::COLUMN_ID_MEMBER])) {
				continue;
			}

			$likesHandled[$row[LikeEntity::COLUMN_ID_MEMBER]] = $this->buildLikeData($row);
		}

		return $likesHandled;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function likeContent(LikesEnum | string $type, int $contentId, int $userId): LikeHandledEntity
	{
		$likeEntity = new LikeEntity();
		$likeEntity->setContentType($type);
		$likeEntity->setContentId($contentId);
		$likeEntity->setIdMember($userId);

		$isContentAlreadyLiked = $this->isContentAlreadyLiked($likeEntity);
		$isContentAlreadyLiked ?
			$this->deleteByContent($likeEntity) :
			$this->insert($likeEntity);

		return $this->buildLikeData($likeEntity);
	}

	public function getById(int $id): array
	{
		return [];
	}
}
