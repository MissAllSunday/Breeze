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

	protected int $id = 0;
	protected string $file = '';
	protected string $class = '';
	protected array $data = [];
	protected string $claimedTime = '';

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
