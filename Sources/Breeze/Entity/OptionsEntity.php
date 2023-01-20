<?php

declare(strict_types=1);


namespace Breeze\Entity;

class OptionsEntity extends BaseEntity implements BaseEntityInterface
{
	public const TABLE = 'breeze_options';
	public const COLUMN_MEMBER_ID = 'member_id';
	public const COLUMN_VARIABLE = 'variable';
	public const COLUMN_VALUE = 'value';
	public const CACHE_NAME = 'user_settings_%d';

	public static function getColumns(): array
	{
		return [
			self::COLUMN_MEMBER_ID,
			self::COLUMN_VARIABLE,
			self::COLUMN_VALUE,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
