<?php

declare(strict_types=1);

namespace Breeze\Database;

interface ClientInterface
{
	public function fetch(): array;

	public function insert(string $tableName, array $columns, array $data, array $bindParams): int;

	public function update(string $tableName, string $queryString, array $bindParams): int;

	public function delete(): int;
}
