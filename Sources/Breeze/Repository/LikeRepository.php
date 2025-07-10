<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Breeze;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\LikeHandledEntity;
use Breeze\LikesEnum;
use Breeze\PermissionsEnum;
use Breeze\Traits\TimeTrait;
use DateMalformedStringException;

class LikeRepository extends BaseRepository implements LikeRepositoryInterface
{
 use TimeTrait;

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
	 * @param array $contentIds [int]
	 * @return array [LikeHandledEntity]
	 */
	public function getByContent(LikesEnum $type, array $contentIds): array
	{
		$likes = [];
		$usersIds = [];

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
			$likes[$row[LikeEntity::COLUMN_ID]][$row[LikeEntity::COLUMN_ID_MEMBER]] = new LikeHandledEntity($row);
			$usersIds[] = $row[LikeEntity::COLUMN_ID_MEMBER];
		}
		$this->dbClient->freeResult($request);

		$this->loadedUsers = $this->loadUsersInfo($usersIds);

		return array_map(function ($likeData) {
			return $this->buildLikeData($likeData, count($likeData));
		}, $likes);
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

		$this->loadedUsers = $this->loadUsersInfo([$likeEntity->getIdMember()]);

		return $this->buildLikeData([$likeEntity], $this->count($likeEntity));
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
	 * @param array $likeData [LikeHandledEntity]
	 */
	public function buildLikeData(array $likeData, int $likesCount): LikeHandledEntity
	{
		$alreadyLiked = $likesCount > 0;
		$likesTextCount = $likesCount;
		$likeHandledEntity = array_shift($likeData);
		$usersToLoad = array_column($likeData, LikeEntity::COLUMN_ID_MEMBER);
		$usersData = [];

		$base = LikeEntity::IDENTIFIER;
		if ($alreadyLiked) {
			$base = 'you_' . $base;
			$likesTextCount = $likesCount - 1;
		}

		$base .= ($this->getText($base . $likesTextCount) !== '') ? $likesTextCount : 'n';

		if (!empty($this->loadedUsers)) {
			$usersData = array_intersect_key($this->loadedUsers, array_flip($usersToLoad));
		}

		$likeHandledEntity->setCanLike($this->isAllowedTo(PermissionsEnum::LIKES_LIKE));
		$likeHandledEntity->setAlreadyLiked($alreadyLiked);
		$likeHandledEntity->setCount($likesCount);
		$likeHandledEntity->setAdditionalInfo([
			'text' => sprintf(
				$this->getText($base),
				$this->commaFormat((string) $likesTextCount)
			),
			'href' => $this->parserText(
				'{scriptUrl}?action=likes;sa=view;ltype={ltype};like={likeId}',
				[
					'ltype' => $likeHandledEntity->getContentType()->value,
					'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
					'likeId' => $likeHandledEntity->getContentId(),
				]
			),
			'usersData' => $usersData,
		]);

		return $likeHandledEntity;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function likeContent(LikesEnum $type, int $contentId, int $userId): LikeHandledEntity
	{
		$likeHandledEntity = new LikeHandledEntity();
		$likeHandledEntity->setContentType($type);
		$likeHandledEntity->setContentId($contentId);
		$likeHandledEntity->setIdMember($userId);

		$isContentAlreadyLiked = $this->isContentAlreadyLiked($likeHandledEntity);
		$this->loadedUsers = $this->loadUsersInfo([$userId]);

		if ($isContentAlreadyLiked) {
			$this->deleteByContent($likeHandledEntity);

			$count = $this->count($likeHandledEntity);

			return $this->buildLikeData([$likeHandledEntity], $count);
		}

			return $this->insert($likeHandledEntity);
	}

	public function getById(int $id): null
	{
		return null;
	}
}
