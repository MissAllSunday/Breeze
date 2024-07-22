<?php

declare(strict_types=1);


namespace Breeze\Entity;

class OptionsEntity extends NormalizeEntity implements BaseEntityInterface
{
	public const TABLE = 'breeze_options';
	public const COLUMN_MEMBER_ID = 'member_id';
	public const COLUMN_VARIABLE = 'variable';
	public const COLUMN_VALUE = 'value';

	public const PROPERTY_MEMBER_ID = 'memberId';
	public const PROPERTY_VARIABLE = 'variable';
	public const PROPERTY_VALUE = 'value';
	public const CACHE_NAME = 'user_settings_%d';
	public const KEY_MAP = [
		self::COLUMN_MEMBER_ID => self::PROPERTY_MEMBER_ID,
		self::COLUMN_VARIABLE => self::PROPERTY_VARIABLE,
		self::COLUMN_VALUE => self::PROPERTY_VALUE,
	];

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

	public function getColumnMap(): array
	{
		return self::KEY_MAP;
	}
}
