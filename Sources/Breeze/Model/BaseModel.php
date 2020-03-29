<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Database\ClientInterface;

abstract class BaseModel implements ModelInterface
{
	/**
	 * @var ClientInterface
	 */
	protected $dbClient;

	public function __construct(ClientInterface $databaseClient)
	{
		$this->dbClient = $databaseClient;
	}

	public function getInsertedId(): int
	{
		return $this->dbClient->getInsertedId($this->getTableName(), $this->getColumnId());
	}

	function getLastValue(): array
	{
		$data = [];

		$result = $this->dbClient->query('
			SELECT ' . implode(', ', $this->getColumns()) . '
			FROM {db_prefix}' . $this->getTableName() . '
			ORDER BY {raw:sort}
			LIMIT {int:limit}', ['sort' => $this->getColumnId() . ' DESC', 'limit' => 1]);

		while ($row = $this->dbClient->fetchAssoc($result))
			$data = $row;

		$this->dbClient->freeResult($result);

		return $data;
	}

	function delete(array $ids): bool
	{
		$this->dbClient->delete(
			$this->getTableName(),
			'
			WHERE ' . $this->getColumnId() . ' IN({array_int:ids})',
			['ids' => array_map('intval', $ids), ]
		);

		return true;
	}

	public function updateLikes(int $contentId, int $numLikes): void
	{
		$this->dbClient->update(
			$this->getTableName(),
			'
			SET likes = {int:num_likes}
			WHERE ' . $this->getColumnId() . ' = {int:id_content}',
			[
				'id_content' => $contentId,
				'num_likes' => $numLikes,
			]
		);
	}

	public function getChunk(int $start = 0, int $maxIndex = 0, array $whereParams = []): array
	{
		$items = [];
		$queryParams = [
			'start' => $start,
			'maxIndex' => $maxIndex,
		];

		if (!empty($whereParams))
			$queryParams = array_merge($queryParams, $whereParams);

		$request = $this->dbClient->query(
			sprintf(
				'
			SELECT %1$s
			FROM {db_prefix}%2$s
			%3$s
			LIMIT {int:start}, {int:maxIndex}',
				implode(', ', $this->getColumns()),
				$this->getTableName(),
				(!empty($whereParams) ? 'WHERE {string:columnName} IN ({array_int:ids})' : '')
			),
			$queryParams
		);

		while ($row = $this->dbClient->fetchAssoc($request))
			$items[$row[$this->getColumnId()]] = $row;

		$this->dbClient->freeResult($request);

		return $items;
	}

	public function getChunkBy(string $columnName, array $ids, int $start = 0, int $maxIndex = 0): array
	{
		$data = [];

		if (empty($statusIds) || empty($columnName) || !$this->isValidColumn($columnName))
			return $data;

		$request = $this->dbClient->query(
			'
			SELECT ' . implode(', ', $this->getColumns()) . '
			FROM {db_prefix}' . $this->getTableName() . '
			WHERE {string:columnName} IN ({array_int:ids})
			ORDER BY {raw:sort}',
			[
				'ids' => array_map('intval', $ids),
				'columnName' => $columnName,
				'sort' => $this->getColumnId() . ' DESC'
			]
		);

		while ($row = $this->dbClient->fetchAssoc($request))
			$moods[$row[$this->getColumnId()]] = $row;

		$this->dbClient->freeResult($request);

		return $data;
	}

	public function getCount(): int
	{
		$result = $this->dbClient->query('
			SELECT ' . implode(', ', $this->getColumns()) . '
			FROM {db_prefix}' . $this->getTableName(), []);

		$rowCount = $this->dbClient->numRows($result);

		$this->dbClient->freeResult($result);

		return $rowCount;
	}

	public function isValidColumn(string $columnName): bool
	{
		return in_array($columnName, $this->getColumns());
	}
}
