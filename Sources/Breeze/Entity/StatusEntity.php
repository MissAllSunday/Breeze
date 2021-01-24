<?php

declare(strict_types=1);

namespace Breeze\Entity;

class StatusEntity extends BaseEntity implements BaseEntityInterface
{
	const TABLE = 'breeze_status';
	const ID = 'id';
	const WALL_ID = 'wallId';
	const USER_ID = 'userId';
	const CREATED_AT = 'createdAt';
	const BODY = 'body';
	const LIKES = 'likes';

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
