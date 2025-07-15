<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Breeze;
use Breeze\Entity\LikeEntity;
use Breeze\LikesEnum;
use Breeze\PermissionsEnum;
use Breeze\Util\Time;

class LikeRepository extends BaseRepository implements LikeRepositoryInterface
{
	public function getTableName(): string
	{
		return LikeEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return LikeEntity::ID;
	}

	public function getColumnPosterId(): string
	{
		return LikeEntity::ID_MEMBER;
	}

	public function getColumns(): array
	{
		return LikeEntity::getColumns();
	}

	/**
	 * @param array $contentIds [int]
	 * @return array [LikeEntity]
	 */
	public function getByContent(LikesEnum $type, array $contentIds): array
	{
		$likes = [];
		$usersIds = [];

		$request = $this->dbClient->query(
			'
			SELECT ' . implode(', ', LikeEntity::getColumns()) . '
			FROM {db_prefix}' . LikeEntity::TABLE . '
			WHERE ' . LikeEntity::TYPE . ' = {string:type}
				AND ' . LikeEntity::ID . ' IN({array_int:contentIds})',
			[
				'contentIds' => array_map('intval', $contentIds),
				'type' => $type->value,
			]
		);

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$likes[$row[LikeEntity::ID]][$row[LikeEntity::ID_MEMBER]] = LikeEntity::from($row);
			$usersIds[] = $row[LikeEntity::ID_MEMBER];
		}
		$this->dbClient->freeResult($request);

		return array_map(function ($likeData) {
			return $this->buildLikeData($likeData, count($likeData));
		}, $likes);
	}

	public function isContentAlreadyLiked(LikeEntity $likeEntity): bool
	{
		$request = $this->dbClient->query(
			'
			SELECT ' . LikeEntity::ID . '
			FROM {db_prefix}' . LikeEntity::TABLE . '
			WHERE ' . LikeEntity::ID_MEMBER . ' = {int:userId}
				AND ' . LikeEntity::TYPE . ' = {string:type}
				AND ' . LikeEntity::ID . ' = {int:contentId}',
			[
				'userId' => $likeEntity->getIdMember(),
				'type' => $likeEntity->getContentType()->value,
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
			'WHERE ' . LikeEntity::ID . ' = {int:contentId}
				AND ' . LikeEntity::TYPE . ' = {string:type}
				AND ' . LikeEntity::ID_MEMBER . ' = {int:userId}',
			[
				'contentId' => $likeEntity->getContentId(),
				'type' => $likeEntity->getContentType()->value,
				'userId' => $likeEntity->getIdMember(),
			]
		);

		if (!$wasDeleted) {
			throw new InvalidLikeException('error_no_like');
		}
	}

	public function insert(LikeEntity $likeEntity): LikeEntity
	{
		$this->dbClient->insert(LikeEntity::TABLE, [
			LikeEntity::ID => 'int',
			LikeEntity::TYPE => 'string',
			LikeEntity::ID_MEMBER => 'int',
			LikeEntity::TIME => 'int',
		], $likeEntity->toInsert(), [LikeEntity::ID, LikeEntity::TYPE, LikeEntity::ID_MEMBER]);

		return $this->buildLikeData([$likeEntity], $this->count($likeEntity));
	}

	public function count(LikeEntity $likeEntity): int
	{
		$result = $this->dbClient->query(
			'
			SELECT {int:contentId}
			FROM {db_prefix}' . LikeEntity::TABLE . '
				WHERE ' . LikeEntity::ID . ' = {int:contentId}
				AND ' . LikeEntity::TYPE . ' = {string:type}',
			[
				'contentId' => $likeEntity->getContentId(),
				'type' => $likeEntity->getContentType()->value,
			]
		);

		$rowCount = $this->dbClient->numRows($result);

		$this->dbClient->freeResult($result);

		return $rowCount;
	}

	/**
	 * @param array $likeData [LikeEntity]
	 */
	public function buildLikeData(array $likeData, int $likesCount): LikeEntity
	{
		$alreadyLiked = $likesCount > 0;
		$likesTextCount = $likesCount;

		$loadedUsers = $this->loadUsersInfo(array_keys($likeData));
		$usersLikeInfo = [];
		array_walk($likeData, function ($like) use (&$usersLikeInfo, $loadedUsers): void {
			$userId = $like->getIdMember();
			$usersLikeInfo[$userId] = [
				'userData' => $loadedUsers[$userId] ?? [],
				'likeTime' => Time::from($like->getLikeTime()),
			];
		});

		$base = LikeEntity::IDENTIFIER;
		if ($alreadyLiked) {
			$base = 'you_' . $base;
			$likesTextCount = $likesCount - 1;
		}

		$base .= ($this->getText($base . $likesTextCount) !== '') ? $likesTextCount : 'n';

		$LikeEntity = array_shift($likeData);
		$LikeEntity->setCanLike($this->isAllowedTo(PermissionsEnum::LIKES_LIKE));
		$LikeEntity->setAlreadyLiked($alreadyLiked);
		$LikeEntity->setCount($likesCount);
		$LikeEntity->setAdditionalInfo([
			'text' => sprintf(
				$this->getText($base),
				$this->commaFormat((string) $likesTextCount)
			),
			'href' => $this->parserText(
				'{scriptUrl}?action=likes;sa=view;ltype={ltype};like={likeId}',
				[
					'ltype' => $LikeEntity->getContentType()->value,
					'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
					'likeId' => $LikeEntity->getContentId(),
				]
			),
			'usersLikeInfo' => $usersLikeInfo,
		]);

		return $LikeEntity;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function likeContent(LikesEnum $type, int $contentId, int $userId): LikeEntity
	{
		$LikeEntity = LikeEntity::from();
		$LikeEntity->setContentType($type);
		$LikeEntity->setContentId($contentId);
		$LikeEntity->setIdMember($userId);

		$isContentAlreadyLiked = $this->isContentAlreadyLiked($LikeEntity);

		if ($isContentAlreadyLiked) {
			$this->deleteByContent($LikeEntity);

			$count = $this->count($LikeEntity);

			return $this->getByContent($type, [$contentId])[$contentId];
		}

			return $this->insert($LikeEntity);
	}

	public function getById(int $id): null
	{
		return null;
	}
}
