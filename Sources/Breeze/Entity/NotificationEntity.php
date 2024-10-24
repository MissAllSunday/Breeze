<?php

declare(strict_types=1);


namespace Breeze\Entity;

class NotificationEntity extends BaseEntity implements BaseEntityInterface
{
	public const TABLE = 'background_tasks';
	public const COLUMN_ID = 'id_task';
	public const COLUMN_TASK_FILE = 'task_file';
	public const COLUMN_TASK_CLASS = 'task_class';
	public const COLUMN_TASK_DATA = 'task_data';
	public const COLUMN_CLAIMED_TIME = 'claimed_time';

	public const TASK_BACKGROUND_FILE = '$sourcedir/tasks/Breeze-Notify.php';
	public const TASK_BACKGROUND_CLASS = 'Breeze_Notify_Background';
	public const TASK_BACKGROUND_DEFAULT_CLAIMED_TIME = 0;

	public static function getColumns(): array
	{
		return [
			self::TABLE,
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
