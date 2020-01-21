<?php

declare(strict_types=1);

use Breeze\Entity\Status as StatusEntity;

class Status extends Base
{
	function insert(array $data, int $statusId = 0): int
	{
		$this->db['db_insert']('replace', '{db_prefix}' . $this->getTableName() .
			'', [
			    StatusEntity::COLUMN_OWNER_ID => 'int',
			    StatusEntity::COLUMN_POSTER_ID => 'int',
			    StatusEntity::COLUMN_TIME => 'int',
			    StatusEntity::COLUMN_BODY => 'string',
			    StatusEntity::COLUMN_LIKES => 'int',
			], $data, [$this->getColumnId()]);

		return $this->getInsertedId();
	}

	function update(array $data, int $statusId = 0): array
	{
		return [];
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
