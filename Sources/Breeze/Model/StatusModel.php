<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\StatusEntity as StatusEntity;

class StatusModel extends BaseModel implements StatusModelInterface
{
	public function insert(array $data, int $statusId = 0): int
	{
		$this->dbClient->insert(StatusEntity::TABLE, [
			StatusEntity::COLUMN_OWNER_ID => 'int',
			StatusEntity::COLUMN_POSTER_ID => 'int',
			StatusEntity::COLUMN_TIME => 'int',
			StatusEntity::COLUMN_BODY => 'string',
			StatusEntity::COLUMN_LIKES => 'int',
		], $data, StatusEntity::COLUMN_ID);

		return $this->getInsertedId();
	}

	public function getStatusByProfile(array $params): array
	{
		$status = [];
		$queryParams = array_merge([
			'tableName' => StatusEntity::TABLE,
			'columns' => 'bs.' . implode(', bs.', $this->getColumns()),
			'commentColumns' =>	', bc.' . implode(', bc.', CommentEntity::getColumns()),
			'columnName' => StatusEntity::COLUMN_OWNER_ID,
			'commentTable' => CommentEntity::TABLE,
			'commentStatusId' => 'bc.' . CommentEntity::COLUMN_STATUS_ID,
			'columnId' => ' bs.' . StatusEntity::COLUMN_ID,
		], $params);

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}{raw:commentColumns}
			FROM {db_prefix}{raw:tableName} bs
			LEFT JOIN {db_prefix}{raw:commentTable} bc ON {raw:commentStatusId} = {raw:columnId}
			WHERE {raw:columnName} IN ({array_int:ids})
			LIMIT {int:start}, {int:maxIndex}',
			$queryParams
		);

		while ($row = $this->dbClient->fetchAssoc($request))
		{
			$status[$row[$this->getColumnId()]] = array_diff_key($row, array_flip(CommentEntity::getColumns()));
			$status[$row[$this->getColumnId()]]['comments'][$row[CommentEntity::COLUMN_ID]] = array_diff_key($row, array_flip(StatusEntity::getColumns()));
		}


		$this->dbClient->freeResult($request);

		return $status;
	}

	public function update(array $data, int $statusId = 0): array
	{
		return [];
	}

	public function getTableName(): string
	{
		return StatusEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return StatusEntity::COLUMN_ID;
	}

	public function getColumnPosterId(): string
	{
		return StatusEntity::COLUMN_POSTER_ID;
	}

	public function getColumns(): array
	{
		return StatusEntity::getColumns();
	}
}
