<?php

declare(strict_types=1);


namespace Breeze\Entity;

class OptionsBaseEntity extends BaseEntity implements BaseEntityInterface
{
	const TABLE = 'breeze_options';
	const COLUMN_VARIABLE = 'variable';
	const COLUMN_VALUE = 'value';

 public static function getColumns(): array
	{
		return [
			self::COLUMN_VARIABLE,
			self::COLUMN_VALUE,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
