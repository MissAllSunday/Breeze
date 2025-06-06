<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Breeze;
use Breeze\Database\ClientInterface;
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
		protected ClientInterface $dbClient
	) {
	}

	/**
	 * @return LikeHandledEntity[]
	 */
	public function getByContent(string|LikesEnum $type, int $contentId): array
	{
		$likes = [];

		$request = $this->dbClient->query(
			'
			SELECT ' . implode(', ', LikeEntity::getColumns()) . '
			FROM {db_prefix}' . LikeEntity::TABLE . '
			WHERE ' . LikeEntity::COLUMN_TYPE . ' = {string:type}
				AND ' . LikeEntity::COLUMN_ID . ' = {int:contentId}',
			[
				'contentId' => $contentId,
				'type' => $type,
			]
		);

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$likes[] = $this->buildLikeData($row);
		}

		$this->dbClient->freeResult($request);

		return $likes;
	}

	/**
	 * @throws DateMalformedStringException
	 */
	public function getLikeInfo(string $type, int $contentId): array
	{
		$likeInfo = [];
		$likes = $this->getByContent($type, $contentId);
		$usersInfo = $this->loadUsersInfo(array_unique(array_column($likes, LikeEntity::COLUMN_ID_MEMBER)));

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
	public function delete(LikeEntity $likeEntity): void
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
		$likeEntity->setTime(time());

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

	public function appendLikeData(array $items, string $itemIdName): array
	{
		return array_map(function (array $item) use ($itemIdName): array {
			$item['likesInfo'] = $this->buildLikeData([
				LikeEntity::COLUMN_TYPE => $item[LikeEntity::IDENTIFIER . LikeEntity::COLUMN_TYPE],
				LikeEntity::COLUMN_ID => $item[$itemIdName],
				LikeEntity::COLUMN_ID_MEMBER => $item[LikeEntity::IDENTIFIER . LikeEntity::COLUMN_ID_MEMBER],
			]);

			return $item;
		}, $items);
	}

	public function buildLikeData(LikeEntity | LikeHandledEntity | array $likeHandledEntity): LikeHandledEntity {
		$base = LikeEntity::IDENTIFIER;

		if (is_array($likeHandledEntity)) {
			$likeHandledEntity = new LikeHandledEntity($likeHandledEntity);
		} elseif ($likeHandledEntity instanceof LikeEntity) {
			$likeHandledEntity = new LikeHandledEntity($likeHandledEntity->toArray());
		}

		$likeHandledEntity->setCanLike($this->isAllowedTo(PermissionsEnum::LIKES_LIKE));
		$likeHandledEntity->setAlreadyLiked($this->isContentAlreadyLiked($likeHandledEntity));


		$likesCount = $likesTextCount = $this->count($likeHandledEntity);

		if ($likeHandledEntity->isAlreadyLiked()) {
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

		return $likeHandledEntity;
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
			$this->delete($likeEntity) :
			$this->insert($likeEntity);

		return $this->buildLikeData($likeEntity);
	}

	public function getById(int $id): array
	{
		return [];
	}
}
