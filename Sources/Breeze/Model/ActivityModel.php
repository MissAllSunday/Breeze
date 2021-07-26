<?php

declare(strict_types=1);


namespace Breeze\Model;

use Breeze\Entity\ActivityEntity;
use Breeze\Util\Json;

class ActivityModel extends BaseModel implements ActivityModelInterface
{
	public function insert(array $data, int $id = 0): int
	{
		$this->dbClient->insert(ActivityEntity::TABLE, [
			ActivityEntity::NAME,
			ActivityEntity::USER_ID,
			ActivityEntity::CONTENT_ID,
			ActivityEntity::CREATED_AT,
			ActivityEntity::EXTRA,
		], $data, ActivityEntity::ID);

		return $this->getInsertedId();
	}

	public function update(array $data, int $id = 0): array
	{
		$this->dbClient->update(
			ActivityEntity::TABLE,
			'SET ' . ActivityEntity::EXTRA . ' = {string:extra}
			WHERE ' . ActivityEntity::ID . ' = {int:idActivity}',
			[
				'idActivity' => $id,
				'extra' => Json::encode($data),
			]
		);

		return [];
	}

	public function getTableName(): string
	{
		return ActivityEntity::getTableName();
	}

	public function getColumnId(): string
	{
		return ActivityEntity::ID;
	}

	public function getColumns(): array
	{
		return ActivityEntity::getColumns();
	}

	public function getByIds(array $activityIds = []): array
	{
		$activities = [];
		$queryParams = array_merge($this->getDefaultQueryParams(), [
			'columnName' => ActivityEntity::ID,
			'activityIds' => $activityIds,
		]);

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:tableName}
			WHERE {raw:columnName} ({array_int:activityIds})
			LIMIT 1',
			$queryParams
		);

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$activities[$row[ActivityEntity::ID]] = $row;
		}

		$this->dbClient->freeResult($request);

		return $activities;
	}
}
