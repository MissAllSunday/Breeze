<?php

declare(strict_types=1);


namespace Breeze\Entity;

class MoodEntity extends BaseEntity implements BaseEntityInterface
{
	public const TABLE = 'breeze_moods';
	public const ID = 'id';
	public const EMOJI = 'emoji';
	public const DESC = 'description';
	public const STATUS = 'isActive';

	public const STATUS_ACTIVE = 1;
	public const STATUS_INACTIVE = 0;

	public static function getStatus(): array
	{
		return [
			self::STATUS_ACTIVE,
			self::STATUS_INACTIVE,
		];
	}

	public static function getColumns(): array
	{
		return [
			self::ID,
			self::EMOJI,
			self::DESC,
			self::STATUS,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
