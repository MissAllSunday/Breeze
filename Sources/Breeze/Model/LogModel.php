<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\LogEntity as LogEntity;
use Breeze\Util\Json;

class LogModel extends BaseModel implements LogModelInterface
{
	function insert(array $data, int $id = 0): int
	{
		if (empty($data)) {
			return 0;
		}

		$data['extra'] = !empty($data['extra']) ? Json::encode($data['extra']) : '';

		$this->dbClient->insert(
			$this->getTableName(),
			[
				LogEntity::COLUMN_MEMBER => 'int',
				LogEntity::COLUMN_CONTENT_TYPE => 'string',
				LogEntity::COLUMN_CONTENT_ID => 'int',
				LogEntity::COLUMN_TIME => 'int',
				LogEntity::COLUMN_EXTRA => 'string'
			],
			$data,
			LogEntity::COLUMN_ID
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

		if (empty($userIds) || empty($maxIndex)) {
			return $logs;
		}

		$result = $this->dbClient->query(
			'
			SELECT ' . implode(', ', $this->getColumns()) . '
			FROM {db_prefix}' . $this->getTableName() . '
			WHERE ' . LogEntity::COLUMN_MEMBER . ' IN ({array_int:userIds})
			ORDER BY ' . $this->getColumnId() . ' DESC
			LIMIT {int:start}, {int:maxIndex}',
			[
				'start' => $start,
				'maxIndex' => $maxIndex,
				'userIds' => $userIds
			]
		);

		while ($row = $this->dbClient->fetchAssoc($result)) {
			$logs[$row[LogEntity::COLUMN_ID]] = [
				'id' => $row[LogEntity::COLUMN_ID],
				'member' => $row[LogEntity::COLUMN_MEMBER],
				'content_type' => $row[LogEntity::COLUMN_CONTENT_TYPE],
				'content_id' => $row[LogEntity::COLUMN_CONTENT_ID],
				'time' => $row[LogEntity::COLUMN_TIME],
				'time_raw' => $row[LogEntity::COLUMN_TIME],
				'extra' => !empty($row[LogEntity::COLUMN_EXTRA]) ?
					json_decode($row[LogEntity::COLUMN_EXTRA], true) : [],
			];
		}

		$this->dbClient->freeResult($result);

		return $logs;
	}

	function getTableName(): string
	{
		return LogEntity::TABLE;
	}

	function getColumnId(): string
	{
		return LogEntity::COLUMN_ID;
	}

	function getColumns(): array
	{
		return LogEntity::getColumns();
	}
}
