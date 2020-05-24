<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\CommentEntity as CommentEntity;

class CommentModel extends BaseModel implements CommentModelInterface
{
	function insert(array $data, int $commentID = 0): int
	{
		if (empty($data))
			return 0;

		$this->dbClient->insert(
			CommentEntity::TABLE,
			[
				CommentEntity::COLUMN_STATUS_ID => 'int',
				CommentEntity::COLUMN_STATUS_OWNER_ID => 'int',
				CommentEntity::COLUMN_POSTER_ID => 'int',
				CommentEntity::COLUMN_PROFILE_ID => 'int',
				CommentEntity::COLUMN_TIME => 'int',
				CommentEntity::COLUMN_BODY => 'string',
				CommentEntity::COLUMN_LIKES => 'int',
			],
			$data,
			CommentEntity::COLUMN_ID
		);

		return $this->getInsertedId();
	}

	public function deleteByStatusID(array $ids): bool
	{
		$this->dbClient->delete(
			CommentEntity::TABLE,
			'WHERE ' . CommentEntity::COLUMN_STATUS_ID . ' IN({array_int:ids})',
			['ids' => $ids]
		);

		return true;
	}

	public function getByProfiles(array $profileOwnerIds): array
	{
		$queryParams = array_merge($this->getDefaultQueryParams(), [
			'columnName' => CommentEntity::COLUMN_PROFILE_ID,
			'profileIds' => $profileOwnerIds,
		]);

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:tableName}
			WHERE {raw:columnName} IN({_array_int:profileId})',
			$queryParams
		);

		return $this->prepareData($request);
	}

	public function getByIds(array $commentIds = []): array
	{
		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:tableName}
			WHERE {raw:columnName} IN ({array_int:ids})
			LIMIT {int:limit}',
			array_merge($this->getDefaultQueryParams(), [
				'limit' => 1,
				'ids' => array_map('intval', $commentIds),
				'columnName' => CommentEntity::COLUMN_ID,
			])
		);

		return $this->prepareData($request);
	}

	public function update(array $data, int $id = 0): array
	{
		return [];
	}

	public function getTableName(): string
	{
		return CommentEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return CommentEntity::COLUMN_ID;
	}

	public function getColumns(): array
	{
		return CommentEntity::getColumns();
	}

	private function prepareData($request, bool $useStatusID = false): array
	{
		$comments = [];
		$usersIds = [];

		while ($row = $this->dbClient->fetchAssoc($request))
		{
			if ($useStatusID)
				$comments[$row[CommentEntity::COLUMN_STATUS_ID]][$row[CommentEntity::COLUMN_ID]] = $row;

			else
				$comments[$row[CommentEntity::COLUMN_ID]] = $row;

			$usersIds[] = $row[CommentEntity::COLUMN_POSTER_ID];
			$usersIds[] = $row[CommentEntity::COLUMN_PROFILE_ID];
			$usersIds[] = $row[CommentEntity::COLUMN_STATUS_OWNER_ID];
		}

		$this->dbClient->freeResult($request);

		return [
			'data' => $comments,
			'usersIds' => array_unique($usersIds),
		];
	}
}
