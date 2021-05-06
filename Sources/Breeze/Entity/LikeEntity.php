<?php

declare(strict_types=1);


namespace Breeze\Entity;

class LikeEntity extends BaseEntity implements BaseEntityInterface
{
	public const TABLE = 'user_likes';

	public const COLUMN_ID_MEMBER = 'id_member';

	public const COLUMN_CONTENT_TYPE = 'content_type';

	public const COLUMN_CONTENT_ID = 'content_id';

	public const COLUMN_LIKE_TIME = 'like_time';

	public static function getColumns(): array
	{
		return [
			self::COLUMN_ID_MEMBER,
			self::COLUMN_CONTENT_TYPE,
			self::COLUMN_CONTENT_ID,
			self::COLUMN_LIKE_TIME,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
