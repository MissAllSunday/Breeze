<?php

declare(strict_types=1);


abstract class Base
{
	protected $db;
	protected $entity;

	public function __construct()
	{
		global $smcFunc;

		$this->db = $smcFunc;
		$this->setEntity();
	}

	function getLastValue(): array
	{
		$data = [];

		$result = $this->db['db_query']('', '
			SELECT ' . implode(', ', $this->getColumns()) . '
			FROM {db_prefix}' . $this->getTableName() . '
			ORDER BY {raw:sort}
			LIMIT {int:limit}', ['sort' => $this->getColumnId() . ' DESC', 'limit' => 1]);

		while ($row = $this->db['db_fetch_assoc']($result))
			$data = $this->generateData($row);

		$this->db['db_free_result']($result);

		return $data;
	}

	function delete(array $ids): bool
	{
		$this->db['db_query']('', '
			DELETE 
			FROM {db_prefix}' . $this->getTableName() . '
			WHERE ' . $this->getColumnId() . ' IN({array_int:ids})', ['id' => $ids, ]);

		return true;
	}

	abstract function insert(): bool;
	abstract function update(int $id): array;
	abstract function getSingleValue(int $id): array;
	abstract function getById(int $id): array;
	abstract function getCount(): int;
	abstract function generateData($row): array;
	abstract function setEntity(): void;
	abstract function getEntity();
	abstract function getTableName(): string;
	abstract function getColumnId(): string;
	abstract function getColumns(): array;
}