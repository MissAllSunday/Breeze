<?php

declare(strict_types=1);


namespace Breeze\Entity;

class AlertEntity extends BaseEntity implements BaseEntityInterface
{
	public const TABLE = 'user_alerts';

	public const COLUMN_ID = 'id_alert';

	public const COLUMN_ALERT_TIME = 'alert_time';

	public const COLUMN_ID_MEMBER = 'id_member';

	public const COLUMN_ID_MEMBER_STARTED = 'id_member_started';

	public const COLUMN_MEMBER_NAME = 'member_name';

	public const COLUMN_CONTENT_TYPE = 'content_type';

	public const COLUMN_CONTENT_ID = 'content_id';

	public const COLUMN_CONTENT_ACTION = 'content_action';

	public const COLUMN_IS_READ = 'is_read';

	public const COLUMN_EXTRA = 'extra';

	public static function getColumns(): array
	{
		return [
			self::COLUMN_ID,
			self::COLUMN_ALERT_TIME,
			self::COLUMN_ID_MEMBER,
			self::COLUMN_ID_MEMBER_STARTED,
			self::COLUMN_MEMBER_NAME,
			self::COLUMN_CONTENT_TYPE,
			self::COLUMN_CONTENT_ID,
			self::COLUMN_CONTENT_ACTION,
			self::COLUMN_IS_READ,
			self::COLUMN_EXTRA,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
