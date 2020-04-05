<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\LikeBaseEntity as LikeEntity;

class LikeModel extends BaseBaseModel implements LikeModelInterface
{
	function insert(array $data, int $id = 0): int
	{
		return 1;
	}

	function update(array $data, int $idContent = 0): array
	{
		$this->dbClient->update(
			LikeBaseEntity::TABLE,
			'SET likes = {int:num_likes}
			WHERE ' . LikeBaseEntity::COLUMN_CONTENT_ID . ' = {int:idContent}',
			[
				'idContent' => $idContent,
			]
		);

		return [];
	}

	public function getByContent(string $type, int $contentId): array
	{
		$likes = [];

		$request = $this->dbClient->query(
			'
			SELECT ' . implode(', ', LikeBaseEntity::getColumns()) . '
			FROM {db_prefix}' . LikeBaseEntity::TABLE . '
			WHERE ' . LikeBaseEntity::COLUMN_CONTENT_TYPE . ' = {string:type}
				AND ' . LikeBaseEntity::COLUMN_CONTENT_ID . ' = {int:contentId}',
			[
				'contentId' => $contentId,
				'type' => $type,
			]
		);

		while ($row = $this->dbClient->fetchAssoc($request))
			$likes[] = $row;

		$this->dbClient->freeResult($request);

		return $likes;
	}

	public function userLikes(string $type, int $userId = 0): array
	{
		$likes = [];

		$request = $this->dbClient->query(
			'
			SELECT ' . LikeBaseEntity::COLUMN_CONTENT_ID . '
			FROM {db_prefix}' . LikeBaseEntity::TABLE . '
			WHERE ' . LikeBaseEntity::COLUMN_ID_MEMBER . ' = {int:userId}
				AND ' . LikeBaseEntity::COLUMN_CONTENT_TYPE . ' = {string:type}',
			[
				'userId' => $userId,
				'type' => $type,
			]
		);

		while ($row = $this->dbClient->fetchAssoc($request))
			$likes[$userId][$type][] = (int) $row[LikeBaseEntity::COLUMN_CONTENT_ID];

		$this->dbClient->freeResult($request);

		return $likes;
	}

	function getTableName(): string
	{
		return LikeBaseEntity::TABLE;
	}

	function getColumnId(): string
	{
		return LikeBaseEntity::COLUMN_ID_MEMBER;
	}

	function getColumns(): array
	{
		return LikeBaseEntity::getColumns();
	}
}
