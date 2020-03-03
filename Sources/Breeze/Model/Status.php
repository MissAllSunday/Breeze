<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\StatusEntity as StatusEntity;

class Status extends BaseModel
{
	public function insert(array $data, int $statusId = 0): int
	{
		$this->db->insert(StatusEntity::TABLE, [
		    StatusEntity::COLUMN_OWNER_ID => 'int',
		    StatusEntity::COLUMN_POSTER_ID => 'int',
		    StatusEntity::COLUMN_TIME => 'int',
		    StatusEntity::COLUMN_BODY => 'string',
		    StatusEntity::COLUMN_LIKES => 'int',
		], $data, StatusEntity::COLUMN_ID);

		return $this->getInsertedId();
	}

	public function getById(array $statusIds): array
	{
		$status = [];

		if (empty($statusIds))
			return $status;

		$request = $this->db->query(
		    '
			SELECT ' . implode(', ', StatusEntity::getColumns()) . '
			FROM {db_prefix}' . StatusEntity::TABLE . '
			WHERE ' . StatusEntity::COLUMN_ID . ' IN ({array_int:statusIds})',
		    ['statusIds' => array_map('intval', $statusIds)]
		);

		while ($row = $this->db->fetchAssoc($request))
			$moods[$row[StatusEntity::COLUMN_ID]] = $row;

		$this->db->freeResult($request);

		return $status;
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
