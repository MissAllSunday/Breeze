<?php

declare(strict_types=1);


use Breeze\Entity\Comment as CommentEntity;

class Comment extends Base
{

	function insert(array $data): int
	{
		$id = 0;

		$this->db['db_insert']('replace', '{db_prefix}' . $this->getTableName() .
			'', [
			    'comments_status_id' => 'int',
			    'comments_status_owner_id' => 'int',
			    'comments_poster_id' => 'int',
			    'comments_profile_id' => 'int',
			    'comments_time' => 'int',
			    'comments_body' => 'string',
			    'likes' => 'int',
			], $data, [$this->getColumnId()]);

		return $this->db['db_insert_id']('{db_prefix}' . $this->getTableName(), $this->getColumnId());
	}

	public function deleteByStatusID(array $ids): bool
	{
		$this->db['db_query']('', '
			DELETE FROM {db_prefix}' . CommentEntity::TABLE . '
			WHERE ' . CommentEntity::COLUMN_STATUS_ID . ' = {array_int:ids}', ['ids' => $ids, ]);

		return true;
	}

	function update(int $id): array
	{
		// TODO: Implement update() method.
	}

	function getSingleValue(int $id): array
	{
		// TODO: Implement getSingleValue() method.
	}

	function getById(int $id): array
	{
		// TODO: Implement getById() method.
	}

	function getCount(): int
	{
		// TODO: Implement getCount() method.
	}

	function generateData($row): array
	{
		// TODO: Implement generateData() method.
	}

	function setEntity(): void
	{
		$this->entity = new CommentEntity();
	}

	function getEntity(): CommentEntity
	{
		return $this->entity;
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
		return $this->getEntity()->getColumns();
	}
}