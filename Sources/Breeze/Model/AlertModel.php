<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Breeze as Breeze;
use Breeze\Entity\AlertBaseEntity as AlertEntity;

class AlertModel extends BaseBaseModel implements AlertModelInterface
{
	function insert(array $data, int $id = 0): int
	{
		if (empty($data))
			return 0;

		if (false !== strpos($data['content_type'], Breeze::PATTERN))
			$params['content_type'] = Breeze::PATTERN . $data['content_type'];

		$this->dbClient->insert(AlertBaseEntity::TABLE, [
			AlertBaseEntity::COLUMN_ALERT_TIME => 'int',
			AlertBaseEntity::COLUMN_ID_MEMBER => 'int',
			AlertBaseEntity::COLUMN_ID_MEMBER_STARTED => 'int',
			AlertBaseEntity::COLUMN_MEMBER_NAME => 'string',
			AlertBaseEntity::COLUMN_CONTENT_TYPE => 'string',
			AlertBaseEntity::COLUMN_CONTENT_ID => 'int',
			AlertBaseEntity::COLUMN_CONTENT_ACTION => 'string',
			AlertBaseEntity::COLUMN_IS_READ => 'int',
			AlertBaseEntity::COLUMN_EXTRA => 'string'
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

		$this->dbClient->update(
			AlertBaseEntity::TABLE,
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

		$request = $this->dbClient->query(
			'
			SELECT ' . AlertBaseEntity::COLUMN_ID . '
			FROM {db_prefix}' . AlertBaseEntity::TABLE . '
			WHERE ' . AlertBaseEntity::COLUMN_ID_MEMBER . ' = {int:userId}
				AND ' . AlertBaseEntity::COLUMN_IS_READ . ' = 0
				AND ' . AlertBaseEntity::COLUMN_CONTENT_TYPE . ' = {string:alertType}
				' . ($alertId ? 'AND ' . AlertBaseEntity::COLUMN_CONTENT_ID . ' = {int:alertId}' : '') . '
				' . ($alertSender ? 'AND ' . AlertBaseEntity::COLUMN_ID_MEMBER_STARTED . ' = {int:alertSender}' : '') . '',
			[
				'userId' => $userId,
				'alertType' => $alertType,
				'alertId' => $alertId,
				'alertSender' => $alertSender,
			]
		);

		$result = $this->dbClient->fetchAssoc($request);

		$this->dbClient->freeResult($request);

		return (bool) $result;
	}

	function getTableName(): string
	{
		return AlertBaseEntity::TABLE;
	}

	function getColumnId(): string
	{
		return AlertBaseEntity::COLUMN_ID;
	}

	function getColumns(): array
	{
		return AlertBaseEntity::getColumns();
	}
}
