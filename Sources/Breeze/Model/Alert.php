<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Breeze as Breeze;
use Breeze\Entity\AlertEntity as AlertEntity;

class Alert extends BaseModel
{
	function insert(array $data, int $id = 0): int
	{
		if (empty($data))
			return 0;

		if (false !== strpos($data['content_type'], Breeze::PATTERN))
			$params['content_type'] = Breeze::PATTERN . $data['content_type'];

		$this->db->insert(AlertEntity::TABLE, [
		    AlertEntity::COLUMN_ALERT_TIME => 'int',
		    AlertEntity::COLUMN_ID_MEMBER => 'int',
		    AlertEntity::COLUMN_ID_MEMBER_STARTED => 'int',
		    AlertEntity::COLUMN_MEMBER_NAME => 'string',
		    AlertEntity::COLUMN_CONTENT_TYPE => 'string',
		    AlertEntity::COLUMN_CONTENT_ID => 'int',
		    AlertEntity::COLUMN_CONTENT_ACTION => 'string',
		    AlertEntity::COLUMN_IS_READ => 'int',
		    AlertEntity::COLUMN_EXTRA => 'string'
		], $data, $this->getColumnId());

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

		$this->db->update(
		    AlertEntity::TABLE,
		    'SET ' . ($updateString) . '
			WHERE ' . $this->getColumnId() . ' = {int:id}',
		    ['id' => $alertId]
		);

		return $this->getAlertById($alertId);
	}

	public function getAlertById(int $alertId): array
	{
		return [$alertId];
	}

	public function checkAlert(int $userId, string $alertType, int $alertId = 0, string $alertSender = ''): bool
	{
		$alreadySent = false;

		if (empty($userId) || empty($alertType))
			return $alreadySent;

		$request = $this->db->query(
		    '
			SELECT ' . AlertEntity::COLUMN_ID . '
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

		$result = $this->db->fetchAssoc($request);

		$this->db->freeResult($request);

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
