<?php

declare(strict_types=1);


namespace Breeze\Entity;

class LikeEntity extends BaseEntity implements BaseEntityInterface
{
	public const TABLE = 'user_likes';

	public const ID_MEMBER = 'id_member';

	public const TYPE = 'content_type';

	public const ID = 'content_id';

	public const TIME = 'like_time';

	public const PARAM_LIKE = 'like';

	public const PARAM_SA = 'sa';

	public const TYPE_STATUS = 'br_sta';

	public static function getTypes(): array
	{
		return [
			self::TYPE_STATUS,
		];
	}

	public static function getColumns(): array
	{
		return [
			self::ID_MEMBER,
			self::TYPE,
			self::ID,
			self::TIME,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
