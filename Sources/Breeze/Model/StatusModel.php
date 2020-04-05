<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\StatusBaseEntity as StatusEntity;

class StatusModel extends BaseBaseModel implements StatusModelInterface
{
	public function insert(array $data, int $statusId = 0): int
	{
		$this->dbClient->insert(StatusBaseEntity::TABLE, [
			StatusBaseEntity::COLUMN_OWNER_ID => 'int',
			StatusBaseEntity::COLUMN_POSTER_ID => 'int',
			StatusBaseEntity::COLUMN_TIME => 'int',
			StatusBaseEntity::COLUMN_BODY => 'string',
			StatusBaseEntity::COLUMN_LIKES => 'int',
		], $data, StatusBaseEntity::COLUMN_ID);

		return $this->getInsertedId();
	}

	public function update(array $data, int $statusId = 0): array
	{
		return [];
	}

	public function getTableName(): string
	{
		return StatusBaseEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return StatusBaseEntity::COLUMN_ID;
	}

	public function getColumnPosterId(): string
	{
		return StatusBaseEntity::COLUMN_POSTER_ID;
	}

	public function getColumns(): array
	{
		return StatusBaseEntity::getColumns();
	}
}
