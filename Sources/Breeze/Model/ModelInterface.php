<?php

declare(strict_types=1);


namespace Breeze\Model;

interface ModelInterface
{
	public function getInsertedId(): int;

	public function insert(array $data, int $id = 0): int;

	public function update(array $data, int $id = 0): array;

	public function getTableName(): string;

	public function getColumnId(): string;

	public function getColumns(): array;

	public function getCount(): int;

	public function getChunk(int $start, int $maxIndex): array;
}
