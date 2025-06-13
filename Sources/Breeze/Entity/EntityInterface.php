<?php

declare(strict_types=1);


namespace Breeze\Entity;

interface EntityInterface
{
	public static function getTableName(): string;

	public static function getColumns(): array;

	public function toArray(): array;
}
