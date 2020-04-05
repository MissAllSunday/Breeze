<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Database\ClientInterface;

abstract class BaseBaseModel implements BaseModelInterface
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
		$items = [];

		$result = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:tableName}
			ORDER BY {string:sort}
			LIMIT {int:limit}',
			array_merge($this->getDefaultQueryParams(), [
				'sort' => $this->getColumnId() . ' DESC',
				'limit' => 1
			])
		);

		while ($row = $this->dbClient->fetchAssoc($result))
			$items = $row;

		$this->dbClient->freeResult($result);

		return $items;
	}

	public function delete(array $ids = []): bool
	{
		$this->dbClient->delete(
			$this->getTableName(),
			'
			WHERE {string:columnName} IN({array_int:ids})',
			[
				'columnName' => $this->getColumnId(),
				'ids' => array_map('intval', $ids),
			]
		);

		return true;
	}

	public function updateLikes(int $contentId, int $numLikes): void
	{
		$this->dbClient->update(
			$this->getTableName(),
			'
			SET likes = {int:num_likes}
			WHERE {string:columnName} = {int:id_content}',
			[
				'columnName' => $this->getColumnId(),
				'id_content' => $contentId,
				'num_likes' => $numLikes,
			]
		);
	}

	public function getChunk(int $start = 0, int $maxIndex = 0, array $whereParams = []): array
	{
		$items = [];
		$whereString = '';
		$queryParams = array_merge($this->getDefaultQueryParams(), [
			'start' => $start,
			'maxIndex' => $maxIndex,
		]);

		if (!empty($whereParams) && $this->isValidColumn($whereParams['columnName']))
		{
			$whereString = 'WHERE {string:columnName} IN ({array_int:ids})';
			$queryParams = array_merge($queryParams, $whereParams);
		}

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:tableName}
			' . $whereString . '
			LIMIT {int:start}, {int:maxIndex}',
			$queryParams
		);

		while ($row = $this->dbClient->fetchAssoc($request))
			$items[$row[$this->getColumnId()]] = $row;

		$this->dbClient->freeResult($request);

		return $items;
	}

	public function getCount(): int
	{
		$result = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:tableName}',
			array_merge($this->getDefaultQueryParams(), [
				'columns' => $this->getColumnId(),
			])
		);

		$rowCount = $this->dbClient->numRows($result);

		$this->dbClient->freeResult($result);

		return $rowCount;
	}

	public function isValidColumn(string $columnName): bool
	{
		return in_array($columnName, $this->getColumns());
	}

	protected function getDefaultQueryParams(): array
	{
		return [
			'columns' => implode(', ', $this->getColumns()),
			'tableName' => $this->getTableName(),
		];
	}
}
