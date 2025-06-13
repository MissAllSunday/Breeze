<?php

declare(strict_types=1);

namespace Breeze\Repository;

use Breeze\Database\ClientInterface;
use Breeze\Entity\AlertEntity as AlertEntity;

class AlertRepository extends BaseRepository implements AlertRepositoryInterface
{
	public function __construct(
		ClientInterface $dbClient
	) {
		parent::__construct($dbClient);
	}

	public function getTableName(): string
	{
		return AlertEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return AlertEntity::COLUMN_ID;
	}

	public function getColumnPosterId(): string
	{
		return AlertEntity::COLUMN_ID_MEMBER_STARTED;
	}

	public function getColumns(): array
	{
		return AlertEntity::getColumns();
	}

	public function insert(AlertEntity $alertEntity): int
	{
		// Don't need the ID (Yet!)
		$alertEntity->unsetIdAlert();
		$alertEntity->setAlertTime(time());

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
		], $alertEntity->toArray(), AlertEntity::COLUMN_ID);

		return $this->dbClient->getInsertedId(AlertEntity::TABLE, AlertEntity::COLUMN_ID);
	}

	public function update(AlertEntity $alertEntity): AlertEntity
	{
		$updateString = $this->buildSetUpdate($alertEntity);
		$id = $alertEntity->getIdAlert();

		$this->dbClient->update(
			AlertEntity::TABLE,
			'SET ' . ($updateString) . '
			WHERE ' . AlertEntity::COLUMN_ID . ' = {int:id}',
			['id' => $id]
		);

		return $this->getById($id);
	}

	public function getById(int $id): AlertEntity
	{
		$request = $this->dbClient->query(
			'
			SELECT ' . implode(', ', AlertEntity::getColumns()) . '
			FROM {db_prefix}' . AlertEntity::TABLE . '
			WHERE ' . AlertEntity::COLUMN_ID . ' = {int:alertId}',
			[
				'alertId' => $id,
			]
		);
		$result = $this->dbClient->fetchAssoc($request);

		$this->dbClient->freeResult($request);

		return new AlertEntity($result);
	}

	public function checkAlert(int $userId, string $alertType, int $alertId = 0, string $alertSender = ''): bool
	{
		if ($userId === 0 || ($alertType === '' || $alertType === '0')) {
			return false;
		}

		$request = $this->dbClient->query(
			'
			SELECT ' . AlertEntity::COLUMN_ID . '
			FROM {db_prefix}' . AlertEntity::TABLE . '
			WHERE ' . AlertEntity::COLUMN_ID_MEMBER . ' = {int:userId}
				AND ' . AlertEntity::COLUMN_IS_READ . ' = 0
				AND ' . AlertEntity::COLUMN_CONTENT_TYPE . ' = {string:alertType}
				' . ($alertId !== 0 ? 'AND ' . AlertEntity::COLUMN_CONTENT_ID . ' = {int:alertId}' : '') . '
				' . ($alertSender !== '' && $alertSender !== '0' ?
				'AND ' . AlertEntity::COLUMN_ID_MEMBER_STARTED . ' = {int:alertSender}' : ''),
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
}
