<?php

declare(strict_types=1);


namespace Breeze\Entity;

class LogEntity extends BaseEntity implements BaseEntityInterface
{
	public const TABLE = 'breeze_logs';

	public const COLUMN_ID = 'id_log';

	public const COLUMN_MEMBER = 'member';

	public const COLUMN_CONTENT_TYPE = 'content_type';

	public const COLUMN_CONTENT_ID = 'content_id';

	public const COLUMN_TIME = 'time';

	public const COLUMN_EXTRA = 'extra';

	public static function getColumns(): array
	{
		return [
			self::COLUMN_ID,
			self::COLUMN_MEMBER,
			self::COLUMN_CONTENT_TYPE,
			self::COLUMN_CONTENT_ID,
			self::COLUMN_TIME,
			self::COLUMN_EXTRA,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
