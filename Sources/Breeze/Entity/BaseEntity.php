<?php

declare(strict_types=1);


namespace Breeze\Entity;

abstract class BaseEntity
{
	function getTableName(): string
	{
		return static::TABLE;
	}

	public abstract static function getColumns(): array;
}
