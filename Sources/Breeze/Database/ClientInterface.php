<?php

declare(strict_types=1);

namespace Breeze\Database;

interface ClientInterface
{
	public function query(string $query, array $bindParams);

	public function fetchAssoc($result): ?array;

	public function numRows($result): int;

	public function freeResult($result): void;

	public function insert(string $tableName, array $columns, array $data, string $columnIdName): void;

	public function getInsertedId(string $tableName, string $columnIdName): int;

	public function update(string $tableName, string $queryString, array $bindParams): int;

	public function delete(string $tableName, string $queryString, array $bindParams): void;
}
