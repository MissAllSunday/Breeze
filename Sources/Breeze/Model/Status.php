<?php

declare(strict_types=1);

use Breeze\Entity\Status as StatusEntity;

class Status extends Base
{
	function insert(array $data, int $statusId = 0): int
	{
		$this->db['db_insert']('replace', '{db_prefix}' . $this->getTableName() .
			'', [
			    'status_owner_id' => 'int',
			    'status_poster_id' => 'int',
			    'status_time' => 'int',
			    'status_body' => 'string',
			    'likes' => 'int',
			], $data, [$this->getColumnId()]);

		return $this->db['db_insert_id']('{db_prefix}' . $this->getTableName(), $this->getColumnId());
	}

	function update(array $data, int $statusId = 0): array
	{
		// TODO: Implement update() method.
	}

	function getTableName(): string
	{
		return StatusEntity::TABLE;
	}

	function getColumnId(): string
	{
		return StatusEntity::COLUMN_ID;
	}

	function getColumns(): array
	{
		return StatusEntity::getColumns();
	}
}