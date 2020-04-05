<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\NotificationBaseEntity as NotificationEntity;

class NotificationModel extends BaseBaseModel implements NotificationModelInterface
{
	function insert(array $data, int $id = 0): int
	{
		if (empty($data))
			return 0;

		$this->dbClient->insert(
			NotificationBaseEntity::TABLE,
			[
				NotificationBaseEntity::COLUMN_TASK_FILE => 'string',
				NotificationBaseEntity::COLUMN_TASK_CLASS => 'string',
				NotificationBaseEntity::COLUMN_TASK_DATA => 'string',
				NotificationBaseEntity::COLUMN_CLAIMED_TIME => 'int'
			],
			$data,
			NotificationBaseEntity::COLUMN_ID
		);

		return $this->getInsertedId();
	}

	function update(array $data, int $id = 0): array
	{
		return [];
	}

	function getTableName(): string
	{
		return NotificationBaseEntity::TABLE;
	}

	function getColumnId(): string
	{
		return NotificationBaseEntity::COLUMN_ID;
	}

	function getColumns(): array
	{
		return NotificationBaseEntity::getColumns();
	}
}
