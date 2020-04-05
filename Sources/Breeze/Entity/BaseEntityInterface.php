<?php

declare(strict_types=1);


namespace Breeze\Entity;

interface BaseEntityInterface
{
	public static function getTableName(): string;

	public static function getColumns(): array;
}
