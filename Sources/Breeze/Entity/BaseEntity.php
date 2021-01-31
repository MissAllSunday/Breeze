<?php

declare(strict_types=1);


namespace Breeze\Entity;

abstract class BaseEntity
{
	public const ALIAS_ID = '%1$s.%2$s AS %2$s';
	public const WRONG_VALUES = 'error_wrong_values';

	abstract public static function getTableName(): string;

	abstract public static function getColumns(): array;
}
