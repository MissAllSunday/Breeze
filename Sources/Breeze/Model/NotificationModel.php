<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\NotificationEntity as NotificationEntity;

class NotificationModel extends BaseModel implements NotificationModelInterface
{
	public function insert(array $data, int $id = 0): int
	{
		if (empty($data)) {
			return 0;
		}

		$this->dbClient->insert(
			NotificationEntity::TABLE,
			[
				NotificationEntity::COLUMN_TASK_FILE => 'string',
				NotificationEntity::COLUMN_TASK_CLASS => 'string',
				NotificationEntity::COLUMN_TASK_DATA => 'string',
				NotificationEntity::COLUMN_CLAIMED_TIME => 'int',
			],
			$data,
			NotificationEntity::COLUMN_ID
		);

		return $this->getInsertedId();
	}

	public function getById(int $statusId): array
	{
		$queryParams = array_merge($this->getDefaultQueryParams(), [
			'columnName' => NotificationEntity::COLUMN_ID,
			'id' => $statusId,
		]);

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:tableName}
			WHERE {raw:columnName} = ({int:id})
			LIMIT 1',
			$queryParams
		);

		return $this->prepareData($request)[0];
	}

	public function update(array $data, int $id = 0): array
	{
		return [];
	}

	public function getTableName(): string
	{
		return NotificationEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return NotificationEntity::COLUMN_ID;
	}

	public function getColumns(): array
	{
		return NotificationEntity::getColumns();
	}

	private function prepareData(object $request): array
	{
		$notifications = [];
		while ($row = $this->dbClient->fetchAssoc($request)) {
			$notifications[$row[NotificationEntity::COLUMN_ID]] = $row;
		}

		$this->dbClient->freeResult($request);

		return $notifications;
	}
}
