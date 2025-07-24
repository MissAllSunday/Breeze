<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Breeze;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\LikeInfoEntity;
use Breeze\LikesEnum;
use Breeze\PermissionsEnum;

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
	 * @return array [LikeInfoEntity]
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
		}
		$this->dbClient->freeResult($request);

		return array_map(function ($likeData) use ($type): LikeInfoEntity {
			return $this->buildLikeInfo($likeData, $type);
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

	public function insert(LikeEntity $likeEntity): LikeInfoEntity
	{
		$this->dbClient->insert(LikeEntity::TABLE, [
			LikeEntity::ID => 'int',
			LikeEntity::TYPE => 'string',
			LikeEntity::ID_MEMBER => 'int',
			LikeEntity::TIME => 'int',
		], $likeEntity->toInsert(), [LikeEntity::ID, LikeEntity::TYPE, LikeEntity::ID_MEMBER]);

		return $this->buildLikeInfo([$likeEntity], $likeEntity->getContentType());
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

	public function buildLikeInfo(array $likeEntities, LikesEnum $type): LikeInfoEntity
	{
		$likesCount = count($likeEntities);
		$likesTextCount = $likesCount;
		$contentId = array_column($likeEntities, LikeEntity::ID)[0];
		$usersIds = array_column($likeEntities, LikeEntity::ID_MEMBER);
		$alreadyLiked = in_array($this->global('user_info')['id'], $usersIds);
		$usersData = $this->loadUsersInfo($usersIds);

		$likeInfo = LikeInfoEntity::from();

		array_walk($likeEntities, function ($like) use ($usersData): void {
			$like->setUserData($usersData[$like->getIdMember()] ?? []);
		});

		$likeInfo->setContentType($type);
		$likeInfo->setLikes($likeEntities);
		$likeInfo->setContentId($contentId);
		$likeInfo->setCanLike($this->isAllowedTo(PermissionsEnum::LIKES_LIKE));
		$likeInfo->setAlreadyLiked($alreadyLiked);

		$base = LikeEntity::IDENTIFIER;
		if ($alreadyLiked) {
			$base = 'you_' . $base;
			$likesTextCount = $likesCount - 1;
		}

		$base .= ($this->getText($base . $likesTextCount) !== '') ? $likesTextCount : 'n';

		$likeInfo->setText(sprintf(
			$this->getText($base),
			$this->commaFormat((string) $likesTextCount)
		));
		$likeInfo->setHref($this->parserText(
			'{scriptUrl}?action=likes;sa=view;ltype={ltype};like={likeId}',
			[
				'ltype' => $type->value,
				'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
				'likeId' => $contentId,
			]
		));

		return $likeInfo;
	}

	/**
	 *@throws InvalidDataException
	 * @throws InvalidLikeException
	 */
	public function likeContent(LikesEnum $type, int $contentId, int $userId): LikeInfoEntity
	{
		$LikeEntity = LikeEntity::from();
		$LikeEntity->setContentType($type);
		$LikeEntity->setContentId($contentId);
		$LikeEntity->setIdMember($userId);

		$isContentAlreadyLiked = $this->isContentAlreadyLiked($LikeEntity);

		if ($isContentAlreadyLiked) {
			$this->deleteByContent($LikeEntity);

			return $this->getByContent($type, [$contentId])[$contentId];
		}

			return $this->insert($LikeEntity);
	}

	public function getById(int $id): null
	{
		return null;
	}
}
