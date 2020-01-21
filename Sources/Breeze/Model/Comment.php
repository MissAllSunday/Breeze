<?php

declare(strict_types=1);

use Breeze\Entity\Comment as CommentEntity;

class Comment extends Base
{
	function insert(array $data, int $commentID = 0): int
	{
		$this->db['db_insert']('replace', '{db_prefix}' . $this->getTableName() .
			'', [
			    CommentEntity::COLUMN_STATUS_ID => 'int',
			    CommentEntity::COLUMN_STATUS_OWNER_ID => 'int',
			    CommentEntity::COLUMN_POSTER_ID => 'int',
			    CommentEntity::COLUMN_PROFILE_ID => 'int',
			    CommentEntity::COLUMN_TIME => 'int',
			    CommentEntity::COLUMN_BODY => 'string',
			    CommentEntity::COLUMN_LIKES => 'int',
			], $data, [$this->getColumnId()]);

		return $this->db['db_insert_id']('{db_prefix}' . $this->getTableName(), $this->getColumnId());
	}

	public function deleteByStatusID(array $ids): bool
	{
		$this->db['db_query']('', '
			DELETE FROM {db_prefix}' . CommentEntity::TABLE . '
			WHERE ' . CommentEntity::COLUMN_STATUS_ID . ' IN({array_int:ids})', ['ids' => $ids]);

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
