<?php

declare(strict_types=1);

use Breeze\Breeze as Breeze;
use Breeze\Database\Client as DatabaseClient;

abstract class Base
{
	protected $db;

	public function __construct()
	{
		$this->db = new DatabaseClient();
	}

	public function getCache(string $key, int $timeToLive = 360): ?array
	{
		return cache_get_data(
		    Breeze::PATTERN . $key,
		    $timeToLive
		);
	}

	public function setCache(string $key, $data, $timeToLive = 360): void
	{
		cache_put_data(Breeze::PATTERN . $key, $data, $timeToLive);
	}

	public function getInsertedId(): int
	{
		return $this->db->getInsertedId($this->getTableName(), $this->getColumnId());
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
			WHERE ' . $this->getColumnId() . ' IN({array_int:ids})', ['ids' => array_map('intval', $ids), ]);

		return true;
	}

	public function updateLikes(int $contentId, int $numLikes): void
	{
		$this->db['db_query'](
		    '',
		    'UPDATE {db_prefix}' . $this->getTableName() . '
			SET likes = {int:num_likes}
			WHERE ' . $this->getColumnId() . ' = {int:id_content}',
		    [
		        'id_content' => $contentId,
		        'num_likes' => $numLikes,
		    ]
		);
	}

	abstract function insert(array $data, int $id = 0): int;

	abstract function update(array $data, int $id = 0): array;

	abstract function getTableName(): string;

	abstract function getColumnId(): string;

	abstract function getColumns(): array;
}
