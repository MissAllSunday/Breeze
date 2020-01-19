<?php

declare(strict_types=1);


use Breeze\Entity\Comment as CommentEntity;

class Comment extends Base
{

	function insert(): bool
	{
		// TODO: Implement insert() method.
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