<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Breeze as Breeze;
use Breeze\Entity\AlertEntity as AlertEntity;

class AlertModel extends BaseModel implements AlertModelInterface
{
	public function insert(array $data, int $id = 0): int
	{
		if (empty($data)) {
			return 0;
		}

		if (str_contains($data['content_type'], Breeze::PATTERN)) {
			$params['content_type'] = Breeze::PATTERN . $data['content_type'];
		}

		$this->dbClient->insert(AlertEntity::TABLE, [
			AlertEntity::COLUMN_ALERT_TIME => 'int',
			AlertEntity::COLUMN_ID_MEMBER => 'int',
			AlertEntity::COLUMN_ID_MEMBER_STARTED => 'int',
			AlertEntity::COLUMN_MEMBER_NAME => 'string',
			AlertEntity::COLUMN_CONTENT_TYPE => 'string',
			AlertEntity::COLUMN_CONTENT_ID => 'int',
			AlertEntity::COLUMN_CONTENT_ACTION => 'string',
			AlertEntity::COLUMN_IS_READ => 'int',
			AlertEntity::COLUMN_EXTRA => 'string',
		], $data, $this->getColumnId());

		return $this->getInsertedId();
	}

	public function update(array $data, int $id = 0): array
	{
		if (empty($data) || empty($id)) {
			return [];
		}

		$updateString = '';
		$paramKeys = array_keys($data);
		$lastKey = key($paramKeys);

		foreach ($data as $column => $newValue) {
			$updateString .= $column . ' = ' . $newValue . ($column != $lastKey ? ', ' : '');
		}

		$this->dbClient->update(
			AlertEntity::TABLE,
			'SET ' . ($updateString) . '
			WHERE ' . $this->getColumnId() . ' = {int:id}',
			['id' => $id]
		);

		return $this->getAlertById($id);
	}

	public function getAlertById(int $alertId): array
	{
		return [$alertId];
	}

	public function checkAlert(int $userId, string $alertType, int $alertId = 0, string $alertSender = ''): bool
	{
		if (empty($userId) || empty($alertType)) {
			return false;
		}

		$request = $this->dbClient->query(
			'
			SELECT ' . AlertEntity::COLUMN_ID . '
			FROM {db_prefix}' . AlertEntity::TABLE . '
			WHERE ' . AlertEntity::COLUMN_ID_MEMBER . ' = {int:userId}
				AND ' . AlertEntity::COLUMN_IS_READ . ' = 0
				AND ' . AlertEntity::COLUMN_CONTENT_TYPE . ' = {string:alertType}
				' . ($alertId ? 'AND ' . AlertEntity::COLUMN_CONTENT_ID . ' = {int:alertId}' : '') . '
				' . ($alertSender ? 'AND ' . AlertEntity::COLUMN_ID_MEMBER_STARTED . ' = {int:alertSender}' : ''),
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

	public function getTableName(): string
	{
		return AlertEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return AlertEntity::COLUMN_ID;
	}

	public function getColumns(): array
	{
		return AlertEntity::getColumns();
	}
}
