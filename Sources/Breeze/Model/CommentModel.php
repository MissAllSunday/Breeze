<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\CommentEntity as CommentEntity;
use Breeze\Entity\StatusEntity as StatusEntity;

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

	public function getStatusByProfile(int $profileOwnerId): array
	{
		$comments = [];
		$usersIds = [];
		$queryParams = array_merge($this->getDefaultQueryParams(), [
			'columnName' => CommentEntity::COLUMN_PROFILE_ID,
			'profileId' => $profileOwnerId,
		]);

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:tableName}
			WHERE {raw:columnName} = {int:profileId}',
			$queryParams
		);

		while ($row = $this->dbClient->fetchAssoc($request))
		{
			$comments[$row[$this->getColumnId()]] =$row;
			$usersIds[] = $row[CommentEntity::COLUMN_POSTER_ID];
			$usersIds[] = $row[CommentEntity::COLUMN_PROFILE_ID];
			$usersIds[] = $row[CommentEntity::COLUMN_STATUS_OWNER_ID];
		}

		$this->dbClient->freeResult($request);

		return [
			'comments' => $comments,
			'usersIds' => array_unique($usersIds),
		];
	}

	function update(array $data, int $id = 0): array
	{
		return [];
	}

	function getTableName(): string
	{
		return CommentEntity::TABLE;
	}

	function getColumnId(): string
	{
		return CommentEntity::COLUMN_ID;
	}

	function getColumns(): array
	{
		return CommentEntity::getColumns();
	}
}
