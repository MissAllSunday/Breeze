<?php

declare(strict_types=1);


namespace Breeze\Entity;

class ActivityEntity extends BaseEntity implements BaseEntityInterface
{
	public const TABLE = 'breeze_activities';
	public const ID = 'id';
	public const NAME = 'name';
	public const USER_ID = 'userId';
	public const CONTENT_ID = 'contentId';
	public const CREATED_AT = 'created_at';
	public const EXTRA = 'extra';

	public static function getColumns(): array
	{
		return [
			self::ID,
			self::NAME,
			self::USER_ID,
			self::CONTENT_ID,
			self::CREATED_AT,
			self::EXTRA,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
