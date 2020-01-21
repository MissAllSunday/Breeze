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

		if (false !== strpos($data['content_type'], Breeze::PATTERN))
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

		return $this->getInsertedId();
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

		return $this->getInsertedId();
	}

	public function checkAlert(int $userId, string $alertType, int $alertId = 0, string $alertSender = ''): bool
	{
		$alreadySent = false;

		if (empty($userId) || empty($alertType))
			return $alreadySent;

		$request = $this->db['db_query'](
		    '',
		    'SELECT ' . AlertEntity::COLUMN_ID . '
			FROM {db_prefix}' . AlertEntity::TABLE . '
			WHERE ' . AlertEntity::COLUMN_ID_MEMBER . ' = {int:userId}
				AND ' . AlertEntity::COLUMN_IS_READ . ' = 0
				AND ' . AlertEntity::COLUMN_CONTENT_TYPE . ' = {string:alertType}
				' . ($alertId ? 'AND ' . AlertEntity::COLUMN_CONTENT_ID . ' = {int:alertId}' : '') . '
				' . ($alertSender ? 'AND ' . AlertEntity::COLUMN_ID_MEMBER_STARTED . ' = {int:alertSender}' : '') . '',
		    [
		        'userId' => $userId,
		        'alertType' => $alertType,
		        'alertId' => $alertId,
		        'alertSender' => $alertSender,
		    ]
		);

		$result = $this->db['db_fetch_row']($request);

		$this->db['db_free_result']($request);

		return (bool) $result;
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
