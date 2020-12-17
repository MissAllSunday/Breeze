<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\LikeEntity as LikeEntity;

class LikeModel extends BaseModel implements LikeModelInterface
{
	public function insert(array $data, int $id = 0): int
	{
		return 1;
	}

	public function update(array $data, int $id = 0): array
	{
		$this->dbClient->update(
			LikeEntity::TABLE,
			'SET likes = {int:num_likes}
			WHERE ' . LikeEntity::COLUMN_CONTENT_ID . ' = {int:idContent}',
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
			WHERE ' . LikeEntity::COLUMN_CONTENT_TYPE . ' = {string:type}
				AND ' . LikeEntity::COLUMN_CONTENT_ID . ' = {int:contentId}',
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
			SELECT ' . LikeEntity::COLUMN_CONTENT_ID . '
			FROM {db_prefix}' . LikeEntity::TABLE . '
			WHERE ' . LikeEntity::COLUMN_ID_MEMBER . ' = {int:userId}
				AND ' . LikeEntity::COLUMN_CONTENT_TYPE . ' = {string:type}',
			[
				'userId' => $userId,
				'type' => $type,
			]
		);

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$likes[$userId][$type][] = (int) $row[LikeEntity::COLUMN_CONTENT_ID];
		}

		$this->dbClient->freeResult($request);

		return $likes;
	}

	public function getTableName(): string
	{
		return LikeEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return LikeEntity::COLUMN_ID_MEMBER;
	}

	public function getColumns(): array
	{
		return LikeEntity::getColumns();
	}
}
