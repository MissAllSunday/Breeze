<?php

declare(strict_types=1);


namespace Breeze\Entity;

class LikeEntity extends BaseEntity
{
	const TABLE = 'user_likes';
	const COLUMN_ID_MEMBER = 'id_member';
	const COLUMN_CONTENT_TYPE = 'content_type';
	const COLUMN_CONTENT_ID = 'content_id';
	const COLUMN_LIKE_TIME = 'like_time';

	public static function getColumns(): array
	{
		return [
		    self::COLUMN_ID_MEMBER,
		    self::COLUMN_CONTENT_TYPE,
		    self::COLUMN_CONTENT_ID,
		    self::COLUMN_LIKE_TIME,
		];
	}
}