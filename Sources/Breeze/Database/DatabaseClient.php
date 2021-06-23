<?php

declare(strict_types=1);

namespace Breeze\Database;

class DatabaseClient implements ClientInterface
{
	protected $db = false;

	public function __construct()
	{
		$this->db = $GLOBALS['smcFunc'];
	}

	public function query(string $query, array $bindParams)
	{
		return $this->db['db_query'](
			'',
			$query,
			$bindParams
		);
	}

	public function queryQuote(string $query, array $bindParams)
	{
		return $this->db['db_quote'](
			'',
			$query,
			$bindParams
		);
	}

	public function fetchAssoc($result): ?array
	{
		return $this->db['db_fetch_assoc']($result);
	}

	public function numRows($result): int
	{
		return $this->db['db_num_rows']($result);
	}

	public function freeResult($result): void
	{
		$this->db['db_free_result']($result);
	}

	public function insert(string $tableName, array $columns, array $data, $columnIdName): void
	{
		ksort($columns);
		ksort($data);

		$this->db['db_insert'](
			'insert',
			'{db_prefix}' . $tableName . '',
			$columns,
			$data,
			(array) $columnIdName
		);
	}

	public function replace(string $tableName, array $columns, array $data, string $columnIdName): void
	{
		$this->db['db_insert'](
			'replace',
			'{db_prefix}' . $tableName . '',
			$columns,
			$data,
			[$columnIdName]
		);
	}

	public function getInsertedId(string $tableName, string $columnIdName): int
	{
		return $this->db['db_insert_id']('{db_prefix}' . $tableName, $columnIdName);
	}

	public function update(string $tableName, string $queryString, array $bindParams): int
	{
		return $this->db['db_query'](
			'',
			'UPDATE {db_prefix}' . $tableName . '
			' . $queryString,
			$bindParams
		);
	}

	public function delete(string $tableName, string $queryString, array $bindParams): bool
	{
		return $this->db['db_query'](
			'',
			'DELETE
			FROM {db_prefix}' . $tableName . '
			' . $queryString,
			$bindParams
		);
	}
}
