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

	public function getById(int $statusId): array
	{
		$queryParams = array_merge($this->getDefaultQueryParams(), [
			'columnName' => StatusEntity::COLUMN_ID,
			'id' => $statusId
		]);

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:tableName}
			WHERE {raw:columnName} = ({int:id})
			LIMIT 1',
			$queryParams
		);

		return $this->prepareData($request);
	}

	public function getStatusByProfile(array $params): array
	{
		$queryParams = array_merge(array_merge($this->getDefaultQueryParams(), [
			'columnName' => StatusEntity::COLUMN_OWNER_ID,
		], $params));

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:tableName}
			WHERE {raw:columnName} IN ({array_int:ids})
			LIMIT {int:start}, {int:maxIndex}',
			$queryParams
		);

		return $this->prepareData($request);
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

	private function prepareData($request): array
	{
		$status = [];
		$usersIds = [];

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$status[$row[StatusEntity::COLUMN_ID]] = array_map(function ($column) {
				return ctype_digit($column) ? ((int) $column) : $column;
			}, $row);

			$usersIds[] = $row[StatusEntity::COLUMN_OWNER_ID];
			$usersIds[] = $row[StatusEntity::COLUMN_POSTER_ID];
		}

		$this->dbClient->freeResult($request);

		return [
			'data' => $status,
			'usersIds' => array_unique($usersIds),
		];
	}
}
