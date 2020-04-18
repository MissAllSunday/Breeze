<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\StatusEntity as StatusEntity;

class StatusModel extends BaseModel implements StatusModelInterface
{
	public function insert(array $data, int $statusId = 0): int
	{
		$this->dbClient->insert(StatusEntity::TABLE, [
			StatusEntity::COLUMN_OWNER_ID => 'int',
			StatusEntity::COLUMN_POSTER_ID => 'int',
			StatusEntity::COLUMN_TIME => 'int',
			StatusEntity::COLUMN_BODY => 'string',
			StatusEntity::COLUMN_LIKES => 'int',
		], $data, StatusEntity::COLUMN_ID);

		return $this->getInsertedId();
	}

	public function update(array $data, int $statusId = 0): array
	{
		return [];
	}

	public function getTableName(): string
	{
		return StatusEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return StatusEntity::COLUMN_ID;
	}

	public function getColumnPosterId(): string
	{
		return StatusEntity::COLUMN_POSTER_ID;
	}

	public function getColumns(): array
	{
		return StatusEntity::getColumns();
	}
}
