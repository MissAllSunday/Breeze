<?php

declare(strict_types=1);

namespace Breeze\Entity;

class StatusEntity extends BaseEntity implements BaseEntityInterface
{
	const TABLE = 'breeze_status';
	const COLUMN_ID = 'status_id';
	const COLUMN_OWNER_ID = 'status_owner_id';
	const COLUMN_POSTER_ID = 'status_poster_id';
	const COLUMN_TIME = 'status_time';
	const COLUMN_BODY = 'status_body';
	const COLUMN_LIKES = 'likes';

	public static function getColumns(): array
	{
		return [
			self::COLUMN_ID,
			self::COLUMN_OWNER_ID,
			self::COLUMN_POSTER_ID,
			self::COLUMN_TIME,
			self::COLUMN_BODY,
			self::COLUMN_LIKES,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
