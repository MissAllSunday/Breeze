<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\CommentBaseEntity as CommentEntity;

class CommentBaseModel extends BaseBaseModel implements BaseModelInterface
{
	function insert(array $data, int $commentID = 0): int
	{
		if (empty($data))
			return 0;

		$this->dbClient->insert(
			CommentBaseEntity::TABLE,
			[
				CommentBaseEntity::COLUMN_STATUS_ID => 'int',
				CommentBaseEntity::COLUMN_STATUS_OWNER_ID => 'int',
				CommentBaseEntity::COLUMN_POSTER_ID => 'int',
				CommentBaseEntity::COLUMN_PROFILE_ID => 'int',
				CommentBaseEntity::COLUMN_TIME => 'int',
				CommentBaseEntity::COLUMN_BODY => 'string',
				CommentBaseEntity::COLUMN_LIKES => 'int',
			],
			$data,
			CommentBaseEntity::COLUMN_ID
		);

		return $this->getInsertedId();
	}

	public function deleteByStatusID(array $ids): bool
	{
		$this->dbClient->delete(
			CommentBaseEntity::TABLE,
			'WHERE ' . CommentBaseEntity::COLUMN_STATUS_ID . ' IN({array_int:ids})',
			['ids' => $ids]
		);

		return true;
	}

	function update(array $data, int $id = 0): array
	{
		return [];
	}

	function getTableName(): string
	{
		return CommentBaseEntity::TABLE;
	}

	function getColumnId(): string
	{
		return CommentBaseEntity::COLUMN_ID;
	}

	function getColumns(): array
	{
		return CommentBaseEntity::getColumns();
	}
}
