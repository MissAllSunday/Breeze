<?php

declare(strict_types=1);


use Breeze\Entity\Log as LogEntity;

class Log extends Base
{

	function insert(array $data, int $id = 0): int
	{
		if (empty($data))
			return 0;

		$data['extra'] = !empty($data['extra']) ? json_encode($data['extra']) : '';

		$this->db['db_insert'](
		    'insert',
		    '{db_prefix}' . $this->getTableName() . '',
		    [
		        LogEntity::COLUMN_MEMBER => 'int',
		        LogEntity::COLUMN_CONTENT_TYPE => 'string',
		        LogEntity::COLUMN_CONTENT_ID => 'int',
		        LogEntity::COLUMN_TIME => 'int',
		        LogEntity::COLUMN_EXTRA => 'string'
		    ],
		    $data,
		    [LogEntity::COLUMN_ID]
		);

		return $this->db['db_insert_id']('{db_prefix}' . $this->getTableName(), $this->getColumnId());
	}

	function update(array $data, int $id = 0): array
	{
		// TODO: Implement update() method.
	}

	public function logCount(array $userIds): int
	{
		$count = 0;

		if (empty($userIds))
			return $count;

		$request = $this->db['db_query'](
		    '',
		    'SELECT id_log
			FROM {db_prefix}' . $this->getTableName() . '
			WHERE ' . LogEntity::COLUMN_MEMBER . ' IN ({array_int:userIds})',
		    ['userIds' => $userIds]
		);
		$count =  $this->db['db_num_rows']($request);

		$this->db['db_free_result']($request);

		return $count;
	}

	public function getLog(array $userIds, int $maxIndex, int $start): array
	{
		$logs = [];

		if (empty($userIds) || empty($maxIndex))
			return $logs;

		$result = $this->db['db_query'](
		    '',
		    'SELECT ' . implode(', ', $this->getColumns()) . '
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

		while ($row = $this->db['db_fetch_assoc']($result))
			$logs[$row[LogEntity::COLUMN_ID]] = [
			    'id' => $row[LogEntity::COLUMN_ID],
			    'member' => $row[LogEntity::COLUMN_MEMBER],
			    'content_type' => $row[LogEntity::COLUMN_CONTENT_TYPE],
			    'content_id' => $row[LogEntity::COLUMN_CONTENT_ID],
			    'time' => timeformat($row[LogEntity::COLUMN_TIME]),
			    'time_raw' => $row[LogEntity::COLUMN_TIME],
			    'extra' => !empty($row[LogEntity::COLUMN_EXTRA]) ?
			    	json_decode($row[LogEntity::COLUMN_EXTRA], true) : [],
			];

		$this->db['db_free_result']($result);

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
		LogEntity::getColumns();
	}
}
