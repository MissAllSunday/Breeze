<?php

declare(strict_types=1);


namespace Breeze\Entity;

class AlertBaseEntity extends BaseEntity implements BaseEntityInterface
{
	const TABLE = 'user_alerts';
	const COLUMN_ID = 'id_alert';
	const COLUMN_ALERT_TIME = 'alert_time';
	const COLUMN_ID_MEMBER = 'id_member';
	const COLUMN_ID_MEMBER_STARTED = 'id_member_started';
	const COLUMN_MEMBER_NAME = 'member_name';
	const COLUMN_CONTENT_TYPE = 'content_type';
	const COLUMN_CONTENT_ID = 'content_id';
	const COLUMN_CONTENT_ACTION = 'content_action';
	const COLUMN_IS_READ = 'is_read';
	const COLUMN_EXTRA = 'extra';

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
