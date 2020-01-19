<?php

declare(strict_types=1);

use Breeze\Breeze as Breeze;
use Breeze\Entity\Alert as AlertEntity;

class Alert extends Base
{
	function insert(array $data, int $id = 0): int
	{
		if (empty($data))
			return 0;

		if (strpos($data['content_type'], Breeze::PATTERN) !== false)
			$params['content_type'] = Breeze::PATTERN . $data['content_type'];

		$this->db['db_insert']('insert', '{db_prefix}' . $this->getTableName() . '', [
		    'alert_time' => 'int',
		    'id_member' => 'int',
		    'id_member_started' => 'int',
		    'member_name' => 'string',
		    'content_type' => 'string',
		    'content_id' => 'int',
		    'content_action' => 'string',
		    'is_read' => 'int',
		    'extra' => 'string'
		], $data, [$this->getColumnId()]);

		return $this->db['db_insert_id']('{db_prefix}' . $this->getTableName(), $this->getColumnId());
	}

	function update(array $data, int $alertId = 0): array
	{
		if (empty($data) || empty($alertId))
			return [];

		$updateString = '';
		$paramKeys = array_keys($data);
		$lastKey = key($paramKeys);

		foreach ($data as $column => $newValue)
			$updateString .= $column . ' = ' . $newValue . ($column != $lastKey ? ', ' : '');

		$this->db['db_query'](
		    '',
		    'UPDATE {db_prefix}' . $this->getTableName() . '
			SET ' . ($updateString) . '
			WHERE ' . $this->getColumnId() . ' = {int:id}',
		    ['id' => $alertId]
		);

		return $this->db['db_insert_id']('{db_prefix}' . $this->getTableName(), $this->getColumnId());
	}

	function getTableName(): string
	{
		return AlertEntity::TABLE;
	}

	function getColumnId(): string
	{
		return AlertEntity::COLUMN_ID;
	}

	function getColumns(): array
	{
		return AlertEntity::getColumns();
	}
}