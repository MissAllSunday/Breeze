<?php

declare(strict_types=1);

namespace Breeze\Database;

class Client implements ClientInterface
{
	protected $db;

	public function __construct()
	{
		global $smcFunc;

		$this->db = $smcFunc;
	}

	public function fetch(): array
	{
		// TODO: Implement fetch() method.
	}

	public function insert(string $tableName, array $columns, array $data, array $columnIndex): int
	{
		$this->db['db_insert'](
		    'insert',
		    '{db_prefix}' . $tableName . '',
		    $columns,
		    $data,
		    $columnIndex
		);
	}

	public function update(string $tableName, string $queryString, array $bindParams): int
	{
		return $this->db['db_query'](
			'',
			'UPDATE {db_prefix}' . $tableName . '
			'. $queryString,
			$bindParams
		);
	}

	public function delete(): int
	{
		// TODO: Implement delete() method.
	}
}
