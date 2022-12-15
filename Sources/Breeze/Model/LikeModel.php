<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\LikeEntity as LikeEntity;

class LikeModel extends BaseModel implements LikeModelInterface
{
	public function insert(array $data, int $id = 0): int
	{
		if (empty($data)) {
			return 0;
		}

		$data[] = time();

		$this->dbClient->insert(LikeEntity::TABLE, [
			LikeEntity::ID => 'int',
			LikeEntity::TYPE => 'string',
			LikeEntity::ID_MEMBER => 'int',
			LikeEntity::TIME => 'int',
		], $data, [LikeEntity::ID, LikeEntity::TYPE, LikeEntity::ID_MEMBER]);

		return $this->getInsertedId();
	}

	public function update(array $data, int $id = 0): array
	{
		$this->dbClient->update(
			LikeEntity::TABLE,
			'SET likes = {int:num_likes}
			WHERE ' . LikeEntity::ID . ' = {int:idContent}',
			[
				'idContent' => $id,
			]
		);

		return [];
	}

	public function getByContent(string $type, int $contentId): array
	{
		$likes = [];

		$request = $this->dbClient->query(
			'
			SELECT ' . implode(', ', LikeEntity::getColumns()) . '
			FROM {db_prefix}' . LikeEntity::TABLE . '
			WHERE ' . LikeEntity::TYPE . ' = {string:type}
				AND ' . LikeEntity::ID . ' = {int:contentId}',
			[
				'contentId' => $contentId,
				'type' => $type,
			]
		);

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$likes[] = $row;
		}

		$this->dbClient->freeResult($request);

		return $likes;
	}

	public function userLikes(string $type, int $userId = 0): array
	{
		$likes = [];

		$request = $this->dbClient->query(
			'
			SELECT ' . LikeEntity::ID . '
			FROM {db_prefix}' . LikeEntity::TABLE . '
			WHERE ' . LikeEntity::ID_MEMBER . ' = {int:userId}
				AND ' . LikeEntity::TYPE . ' = {string:type}',
			[
				'userId' => $userId,
				'type' => $type,
			]
		);

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$likes[$userId][$type][] = (int)$row[LikeEntity::ID];
		}

		$this->dbClient->freeResult($request);

		return $likes;
	}

	public function checkLike(string $type, int $contentId, int $userId): int
	{
		$request = $this->dbClient->query(
			'
			SELECT ' . LikeEntity::ID . '
			FROM {db_prefix}' . LikeEntity::TABLE . '
			WHERE ' . LikeEntity::ID_MEMBER . ' = {int:userId}
				AND ' . LikeEntity::TYPE . ' = {string:type}
				AND ' . LikeEntity::ID . ' = {int:contentId}',
			[
				'userId' => $userId,
				'type' => $type,
				'contentId' => $contentId,
			]
		);

		$numRows = $this->dbClient->numRows($request);

		$this->dbClient->freeResult($request);

		return $numRows;
	}

	public function getTableName(): string
	{
		return LikeEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return LikeEntity::ID_MEMBER;
	}

	public function getColumns(): array
	{
		return LikeEntity::getColumns();
	}

	public function deleteContent(string $type, int $contentId, int $userId): bool
	{
		return $this->dbClient->delete(
			LikeEntity::TABLE,
			'WHERE ' . LikeEntity::ID . ' = {int:contentId}
				AND ' . LikeEntity::TYPE . ' = {string:type}
				AND ' . LikeEntity::ID_MEMBER . ' = {int:userId}',
			[
				'contentId' => $contentId,
				'type' => $type,
				'userId' => $userId,
			]
		);
	}

	public function insertContent(string $type, int $contentId, int $userId): void
	{
		$this->dbClient->insert(LikeEntity::TABLE, [
			LikeEntity::ID => 'int',
			LikeEntity::TYPE => 'string',
			LikeEntity::ID_MEMBER => 'int',
			LikeEntity::TIME => 'int',
		], [
			$contentId,
			$type,
			$userId,
			time(),
		], [LikeEntity::ID, LikeEntity::TYPE, LikeEntity::ID_MEMBER]);
	}

	public function countContent(string $type, int $contentId): int
	{
		$result = $this->dbClient->query(
			'
			SELECT {int:contentId}
			FROM {db_prefix}' . LikeEntity::TABLE . '
				WHERE ' . LikeEntity::ID . ' = {int:contentId}
				AND ' . LikeEntity::TYPE . ' = {string:type}',
			[
				'contentId' => $contentId,
				'type' => $type,
			]
		);

		$rowCount = $this->dbClient->numRows($result);

		$this->dbClient->freeResult($result);

		return $rowCount;
	}
}
