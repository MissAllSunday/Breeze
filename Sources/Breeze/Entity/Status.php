<?php

declare(strict_types=1);

namespace Breeze\Entity;

class Status extends BaseEntity
{
	const TABLE = 'breeze_status';
	const COLUMN_ID = 'id';
	const COLUMN_OWNER_ID = 'owner_id';
	const COLUMN_POSTER_ID = 'poster_id';
	const COLUMN_TIME = 'time';
	const COLUMN_BODY = 'body';
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
}
