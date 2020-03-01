<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\Like as LikeEntity;

class Like extends BaseModel
{
	function insert(array $data, int $id = 0): int
	{
		return 1;
	}

	function update(array $data, int $idContent = 0): array
	{
		$this->db->update(
		    LikeEntity::TABLE,
		    'SET likes = {int:num_likes}
			WHERE ' . LikeEntity::COLUMN_CONTENT_ID . ' = {int:idContent}',
		    [
		        'idContent' => $idContent,
		    ]
		);

		return [];
	}

	public function getByContent(string $type, int $contentId): array
	{
		$likes = [];

		$request = $this->db->query(
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

		while ($row = $this->db->fetchAssoc($request))
			$likes[] = $row;

		$this->db->freeResult($request);

		return $likes;
	}

	public function userLikes(string $type, int $userId = 0): array
	{
		$likes = [];

		$request = $this->db->query(
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

		while ($row = $this->db->fetchAssoc($request))
			$likes[$userId][$type][] = (int) $row[LikeEntity::COLUMN_CONTENT_ID];

		$this->db->freeResult($request);

		return $likes;
	}

	function getTableName(): string
	{
		return LikeEntity::TABLE;
	}

	function getColumnId(): string
	{
		return LikeEntity::COLUMN_ID_MEMBER;
	}

	function getColumns(): array
	{
		return LikeEntity::getColumns();
	}
}
