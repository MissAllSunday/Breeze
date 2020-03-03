<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Database\DatabaseClient;

abstract class BaseModel implements ModelInterface
{
	/**
	 * @var DatabaseClient
	 */
	protected $dbClient;

	public function __construct(DatabaseClient $databaseClient)
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

	public function getChunk(int $start = 0, int $maxIndex = 0): array
	{
		$items = [];

		$request = $this->dbClient->query(
		    '
			SELECT ' . implode(', ', $this->getColumns()) . '
			FROM {db_prefix}' . $this->getTableName() . '
			LIMIT {int:start}, {int:maxIndex}',
		    [
		        'start' => $start,
		        'maxIndex' => $maxIndex,
		    ]
		);

		while ($row = $this->dbClient->fetchAssoc($request))
			$items[$row[$this->getColumnId()]] = $row;

		$this->dbClient->freeResult($request);

		return $items;
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
}
