<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\Comment as CommentEntity;

class Comment extends Base
{
	function insert(array $data, int $commentID = 0): int
	{
		$this->db->insert(
		    $this->getTableName(),
		    [
		        CommentEntity::COLUMN_STATUS_ID => 'int',
		        CommentEntity::COLUMN_STATUS_OWNER_ID => 'int',
		        CommentEntity::COLUMN_POSTER_ID => 'int',
		        CommentEntity::COLUMN_PROFILE_ID => 'int',
		        CommentEntity::COLUMN_TIME => 'int',
		        CommentEntity::COLUMN_BODY => 'string',
		        CommentEntity::COLUMN_LIKES => 'int',
		    ],
		    $data,
		    $this->getColumnId()
		);

		return $this->getInsertedId();
	}

	public function deleteByStatusID(array $ids): bool
	{
		$this->db->delete(
		    CommentEntity::TABLE,
		    'WHERE ' . CommentEntity::COLUMN_STATUS_ID . ' IN({array_int:ids})',
		    ['ids' => $ids]
		);

		return true;
	}

	function update(array $data, int $id = 0): array
	{
		return [];
	}

	function getTableName(): string
	{
		return CommentEntity::TABLE;
	}

	function getColumnId(): string
	{
		return CommentEntity::COLUMN_ID;
	}

	function getColumns(): array
	{
		return CommentEntity::getColumns();
	}
}
