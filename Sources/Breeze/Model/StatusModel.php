<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\StatusEntity as StatusEntity;

class StatusModel extends BaseModel implements StatusModelInterface
{
	public function insert(array $data, int $id = 0): int
	{
		$this->dbClient->insert(StatusEntity::TABLE, [
			StatusEntity::WALL_ID => 'int',
			StatusEntity::USER_ID => 'int',
			StatusEntity::CREATED_AT => 'int',
			StatusEntity::BODY => 'string',
			StatusEntity::LIKES => 'int',
		], $data, StatusEntity::ID);

		return $this->getInsertedId();
	}

	public function getById(int $statusId): array
	{
		$queryParams = array_merge($this->getDefaultQueryParams(), [
			'columnName' => StatusEntity::ID,
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
			'columnName' => StatusEntity::WALL_ID,
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
		return StatusEntity::ID;
	}

	public function getColumnPosterId(): string
	{
		return StatusEntity::USER_ID;
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
			$status[$row[StatusEntity::ID]] = array_map(function ($column) {
				return ctype_digit($column) ? ((int) $column) : $column;
			}, $row);

			$usersIds[] = $row[StatusEntity::WALL_ID];
			$usersIds[] = $row[StatusEntity::USER_ID];
		}

		$this->dbClient->freeResult($request);

		return [
			'data' => $status,
			'usersIds' => array_unique($usersIds),
		];
	}
}
