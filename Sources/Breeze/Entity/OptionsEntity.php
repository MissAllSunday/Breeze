<?php

declare(strict_types=1);


namespace Breeze\Entity;

class OptionsEntity extends BaseEntity implements EntityInterface
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
}
