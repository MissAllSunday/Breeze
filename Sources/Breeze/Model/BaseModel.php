<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Database\ClientInterface;
use Breeze\Entity\LikeEntity;

abstract class BaseModel implements BaseModelInterface
{
	protected ClientInterface $dbClient;

	protected const PARENT_LIKE_IDENTIFIER = 'parent';
	protected const LIKE_IDENTIFIER = 'likes';

	public function __construct(ClientInterface $databaseClient)
	{
		$this->dbClient = $databaseClient;
	}

	public function getInsertedId(): int
	{
		return $this->dbClient->getInsertedId($this->getTableName(), $this->getColumnId());
	}

	public function getLastValue(): array
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
				'limit' => 1,
			])
		);

		while ($row = $this->dbClient->fetchAssoc($result)) {
			$items = $row;
		}

		$this->dbClient->freeResult($result);

		return $items;
	}

	public function delete(array $ids = []): bool
	{
		if ($ids === []) {
			return false;
		}

		return $this->dbClient->delete(
			$this->getTableName(),
			'
			WHERE {raw:columnName} IN({array_int:ids})',
			[
				'columnName' => $this->getColumnId(),
				'ids' => array_map('intval', $ids),
			]
		);
	}

	public function updateLikes(int $contentId, int $numLikes): void
	{
		$this->dbClient->update(
			$this->getTableName(),
			'
			SET likes = {int:num_likes}
			WHERE {raw:columnName} = {int:id_content}',
			[
				'columnName' => $this->getColumnId(),
				'id_content' => $contentId,
				'num_likes' => $numLikes,
			]
		);
	}

	public function getChunk(array $queryParams = []): array
	{
		$items = [];
		$whereString = '';
		$queryParams = array_merge(array_merge($this->getDefaultQueryParams(), [
			'start' => 0,
			'maxIndex' => 0,
		]), $queryParams);

		if (isset($queryParams['columnName']) && $this->isValidColumn($queryParams['columnName'])) {
			$whereString = 'WHERE {raw:columnName} IN ({array_int:ids})';
		}

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:tableName}
			' . $whereString . '
			LIMIT {int:start}, {int:maxIndex}',
			$queryParams
		);

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$items[$row[$this->getColumnId()]] = $row;
		}

		$this->dbClient->freeResult($request);

		return $items;
	}

	public function getCount(array $queryParams = []): int
	{
		$whereString = '';
		if (isset($queryParams['columnName']) && $this->isValidColumn($queryParams['columnName'])) {
			$whereString = 'WHERE {raw:columnName} IN ({array_int:ids})';
		}

		$result = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:tableName}
			' . $whereString,
			array_merge($this->getDefaultQueryParams(), [
				'columns' => $this->getColumnId(),
			], $queryParams)
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

	protected function getDefaultQueryParamsWithLikes(string $type, string $parentIdentifier = 'id'): array
	{
		return [
			'columns' => implode(', ', array_map(function (string $parentColumn): string {
					return self::PARENT_LIKE_IDENTIFIER . '.' . $parentColumn;
			}, $this->getColumns())) . ', ' .
				implode(', ', array_map(function (string $likeColumn) use ($type): string {

					$columnName = self::LIKE_IDENTIFIER . '.' . $likeColumn;

					return match ($likeColumn) {
						LikeEntity::COLUMN_TYPE => 'COALESCE(' . $columnName . ', "' . $type . '")',
						LikeEntity::COLUMN_ID_MEMBER => 'COALESCE(' . $columnName . ', 0)',
						default => $columnName,
					} . ' AS ' . LikeEntity::IDENTIFIER . $likeColumn;
				}, LikeEntity::getColumns())),
			'tableName' => $this->getTableName(),
			'from' => $this->getTableName() . ' AS ' . self::PARENT_LIKE_IDENTIFIER,
			'likeJoin' => LikeEntity::TABLE . ' AS ' . self::LIKE_IDENTIFIER . '
			 	ON (' . self::LIKE_IDENTIFIER . '.' . LikeEntity::COLUMN_ID . ' =
			 	 ' . self::PARENT_LIKE_IDENTIFIER . '.' . $parentIdentifier . '
			 	AND ' . self::LIKE_IDENTIFIER . '.' . LikeEntity::COLUMN_TYPE . ' = "' . $type . '")',
		];
	}
}
