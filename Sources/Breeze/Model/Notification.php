<?php

declare(strict_types=1);


use Breeze\Entity\Notification as NotificationEntity;

class Notification extends Base
{

	function insert(array $data, int $id = 0): int
	{
		global $smcFunc;

		if (empty($data))
			return false;

		$smcFunc['db_insert'](
		    'insert',
		    '{db_prefix}' . NotificationEntity::TABLE . '',
		    [
		        NotificationEntity::COLUMN_TASK_FILE => 'string',
		        NotificationEntity::COLUMN_TASK_CLASS => 'string',
		        NotificationEntity::COLUMN_TASK_DATA => 'string',
		        NotificationEntity::COLUMN_CLAIMED_TIME => 'int'
		    ],
		    $data,
		    [NotificationEntity::COLUMN_ID]
		);

		return $this->getInsertedId();
	}

	function update(array $data, int $id = 0): array
	{
		return [];
	}

	function getTableName(): string
	{
		return NotificationEntity::TABLE;
	}

	function getColumnId(): string
	{
		return NotificationEntity::COLUMN_ID;
	}

	function getColumns(): array
	{
		return NotificationEntity::getColumns();
	}
}
