<?php

declare(strict_types=1);


namespace Breeze\Entity;

class OptionsEntity extends Entity implements EntityInterface
{
	public const string TABLE = 'breeze_options';
	public const string COLUMN_MEMBER_ID = 'member_id';
	public const string COLUMN_VARIABLE = 'variable';
	public const string COLUMN_VALUE = 'value';

	public const string PROPERTY_MEMBER_ID = 'memberId';
	public const string PROPERTY_VARIABLE = 'variable';
	public const string PROPERTY_VALUE = 'value';
	public const string CACHE_NAME = 'user_settings_%d';
	public const array KEY_MAP = [
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

	public function castValue(string $columnName, mixed $value): string|int
	{
		return match ($columnName) {
			self::COLUMN_MEMBER_ID => (int) $value,
			default => (string) $value,
		};
	}
}
