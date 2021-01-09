<?php

declare(strict_types=1);


namespace Breeze\Entity;

class MoodEntity extends BaseEntity implements BaseEntityInterface
{
	public const TABLE = 'breeze_moods';
	public const COLUMN_ID = 'moods_id';
	public const COLUMN_EMOJI = 'emoji';
	public const COLUMN_DESC = 'description';
	public const COLUMN_STATUS = 'active';

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
			self::COLUMN_ID,
			self::COLUMN_EMOJI,
			self::COLUMN_DESC,
			self::COLUMN_STATUS,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
