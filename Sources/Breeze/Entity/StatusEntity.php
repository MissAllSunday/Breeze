<?php

declare(strict_types=1);

namespace Breeze\Entity;

class StatusEntity extends BaseEntity implements BaseEntityInterface
{
	public const TABLE = 'breeze_status';

	public const ID = 'id';

	public const WALL_ID = 'wallId';

	public const USER_ID = 'userId';

	public const CREATED_AT = 'createdAt';

	public const BODY = 'body';

	public const LIKES = 'likes';

	public static function getColumns(): array
	{
		return [
			self::ID,
			self::WALL_ID,
			self::USER_ID,
			self::CREATED_AT,
			self::BODY,
			self::LIKES,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
