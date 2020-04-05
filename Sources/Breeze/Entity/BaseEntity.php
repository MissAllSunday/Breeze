<?php

declare(strict_types=1);


namespace Breeze\Entity;

abstract class BaseEntity
{
	public abstract static function getTableName(): string;

	public abstract static function getColumns(): array;
}
