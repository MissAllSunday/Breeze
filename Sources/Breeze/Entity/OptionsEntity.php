<?php

declare(strict_types=1);


namespace Breeze\Entity;

class OptionsEntity extends Entity implements EntityInterface
{
	public const string TABLE = 'breeze_options';
	public const string MEMBER_ID = 'member_id';
	public const string VARIABLE = 'variable';
	public const string VALUE = 'value';

	public const string PROPERTY_MEMBER_ID = 'memberId';
	public const string PROPERTY_VARIABLE = 'variable';
	public const string PROPERTY_VALUE = 'value';
	public const string CACHE_NAME = 'user_settings_%d';
	public const array KEY_MAP = [
		self::MEMBER_ID => self::PROPERTY_MEMBER_ID,
		self::VARIABLE => self::PROPERTY_VARIABLE,
		self::VALUE => self::PROPERTY_VALUE,
	];

	public static function from(array $data = []): self
	{
		$class = self::class;

		return new $class($data);
	}

	public static function getColumns(): array
	{
		return [
			self::MEMBER_ID,
			self::VARIABLE,
			self::VALUE,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}

	public function castValue(string $columnName, mixed $value): string|int
	{
		return match ($columnName) {
			self::MEMBER_ID => (int) $value,
			default => (string) $value,
		};
	}
}
