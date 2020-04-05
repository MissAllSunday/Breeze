<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\LogBaseEntity as LogEntity;

class LogModel extends BaseBaseModel implements LogModelInterface
{
	function insert(array $data, int $id = 0): int
	{
		if (empty($data))
			return 0;

		$data['extra'] = !empty($data['extra']) ? json_encode($data['extra']) : '';

		$this->dbClient->insert(
			$this->getTableName(),
			[
				LogBaseEntity::COLUMN_MEMBER => 'int',
				LogBaseEntity::COLUMN_CONTENT_TYPE => 'string',
				LogBaseEntity::COLUMN_CONTENT_ID => 'int',
				LogBaseEntity::COLUMN_TIME => 'int',
				LogBaseEntity::COLUMN_EXTRA => 'string'
			],
			$data,
			LogBaseEntity::COLUMN_ID
		);

		return $this->getInsertedId();
	}

	function update(array $data, int $id = 0): array
	{
		return [];
	}

	public function getLog(array $userIds, int $maxIndex, int $start): array
	{
		$logs = [];

		if (empty($userIds) || empty($maxIndex))
			return $logs;

		$result = $this->dbClient->query(
			'
			SELECT ' . implode(', ', $this->getColumns()) . '
			FROM {db_prefix}' . $this->getTableName() . '
			WHERE ' . LogBaseEntity::COLUMN_MEMBER . ' IN ({array_int:userIds})
			ORDER BY ' . $this->getColumnId() . ' DESC
			LIMIT {int:start}, {int:maxIndex}',
			[
				'start' => $start,
				'maxIndex' => $maxIndex,
				'userIds' => $userIds
			]
		);

		while ($row = $this->dbClient->fetchAssoc($result))
			$logs[$row[LogBaseEntity::COLUMN_ID]] = [
				'id' => $row[LogBaseEntity::COLUMN_ID],
				'member' => $row[LogBaseEntity::COLUMN_MEMBER],
				'content_type' => $row[LogBaseEntity::COLUMN_CONTENT_TYPE],
				'content_id' => $row[LogBaseEntity::COLUMN_CONTENT_ID],
				'time' => $row[LogBaseEntity::COLUMN_TIME],
				'time_raw' => $row[LogBaseEntity::COLUMN_TIME],
				'extra' => !empty($row[LogBaseEntity::COLUMN_EXTRA]) ?
					json_decode($row[LogBaseEntity::COLUMN_EXTRA], true) : [],
			];

		$this->dbClient->freeResult($result);

		return $logs;
	}

	function getTableName(): string
	{
		return LogBaseEntity::TABLE;
	}

	function getColumnId(): string
	{
		return LogBaseEntity::COLUMN_ID;
	}

	function getColumns(): array
	{
		return LogBaseEntity::getColumns();
	}
}
