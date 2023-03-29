<?php

declare(strict_types=1);


namespace Breeze\Model;

interface BaseModelInterface
{
	public function getInsertedId(): int;

	public function insert(array $data): int;

	public function update(array $data, int $id = 0): array;

	public function delete(array $ids = []): bool;

	public function getTableName(): string;

	public function getColumnId(): string;

	public function getColumns(): array;

	public function getCount(array $queryParams = []): int;

	public function getChunk(array $queryParams = []): array;
}
