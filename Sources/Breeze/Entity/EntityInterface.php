<?php

declare(strict_types=1);


namespace Breeze\Entity;

use JsonSerializable;

interface EntityInterface extends JsonSerializable
{
	public static function from(array $data = []): self;

	public static function getTableName(): string;

	public static function getColumns(): array;

	public function toArray(): array;

	public function toInsert(): array;

	public function castValue(string $columnName, mixed $value): mixed;
}
