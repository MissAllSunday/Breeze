<?php

declare(strict_types=1);


use Breeze\Entity\Status as StatusEntity;

class Status extends Base
{
	function getCount(): int
	{
	}

	function insert(array $data): int
	{
		$id = 0;

		$this->db['db_insert']('replace', '{db_prefix}' . $this->getTableName() .
			'', [
			    'status_owner_id' => 'int',
			    'status_poster_id' => 'int',
			    'status_time' => 'int',
			    'status_body' => 'string',
			    'likes' => 'int',
			], $data, [$this->getColumnId()]);

		// Get the newly created status ID
		return $this->db['db_insert_id']('{db_prefix}' . $this->getTableName(), $this->getColumnId());
	}

	function update(int $id): array
	{
		// TODO: Implement update() method.
	}


	function getSingleValue(int $id): array
	{

	}

	function getById(int $id): array
	{

	}

	function generateData($row): array
	{
		// TODO: Implement generateData() method.
	}

	function setEntity(): void
	{
		$this->entity = new StatusEntity();
	}

	function getEntity(): StatusEntity
	{
		return $this->entity;
	}

	function getTableName(): string
	{
		// TODO: Implement getTableName() method.
	}

	function getColumnId(): string
	{
		// TODO: Implement getColumnId() method.
	}

	function getColumns(): array
	{
		// TODO: Implement getColumns() method.
	}
}