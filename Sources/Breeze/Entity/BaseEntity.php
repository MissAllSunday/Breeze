<?php

declare(strict_types=1);


namespace Breeze\Entity;

abstract class BaseEntity
{
	public const WRONG_VALUES = 'error_wrong_values';

	public abstract static function getTableName(): string;

	public abstract static function getColumns(): array;
}
