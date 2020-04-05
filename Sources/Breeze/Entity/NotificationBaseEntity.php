<?php

declare(strict_types=1);


namespace Breeze\Entity;

class NotificationBaseEntity extends BaseEntity implements BaseEntityInterface
{
	const TABLE = 'background_tasks';
	const COLUMN_ID = 'id_task';
	const COLUMN_TASK_FILE = 'task_file';
	const COLUMN_TASK_CLASS = 'task_class';
	const COLUMN_TASK_DATA = 'task_data';
	const COLUMN_CLAIMED_TIME = 'claimed_time';

	public static function getColumns(): array
	{
		return [
			self::TABLE ,
			self::COLUMN_ID,
			self::COLUMN_TASK_FILE,
			self::COLUMN_TASK_CLASS,
			self::COLUMN_TASK_DATA,
			self::COLUMN_CLAIMED_TIME,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
