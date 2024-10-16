<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\BaseEntity;
use Breeze\Entity\CommentEntity as CommentEntity;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\StatusEntity;

class CommentModel extends BaseModel implements CommentModelInterface
{
	public function insert(array $data, int $id = 0): int
	{
		if ($data === []) {
			return 0;
		}

		$this->dbClient->insert(
			CommentEntity::TABLE,
			[
				CommentEntity::STATUS_ID => 'int',
				CommentEntity::USER_ID => 'int',
				CommentEntity::CREATED_AT => 'int',
				CommentEntity::BODY => 'string',
				CommentEntity::LIKES => 'int',
			],
			$data,
			CommentEntity::ID
		);

		return $this->getInsertedId();
	}

	public function deleteByStatusId(array $statusIds): bool
	{
		return $this->dbClient->delete(
			CommentEntity::TABLE,
			'WHERE ' . CommentEntity::STATUS_ID . ' IN({array_int:statusIds})',
			['statusIds' => $statusIds]
		);
	}

	public function getByProfiles(array $profileOwnerIds): array
	{
		$queryParams = array_merge($this->getDefaultQueryParamsWithLikes(LikeEntity::TYPE_COMMENT), [
			'columnName' => StatusEntity::WALL_ID,
			'profileIds' => $profileOwnerIds,
			'statusTable' => StatusEntity::TABLE,
			'compare' => StatusEntity::TABLE .
				'.' . StatusEntity::ID . ' = ' . self::PARENT_LIKE_IDENTIFIER . '.' . CommentEntity::STATUS_ID,
		]);

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:from}
				JOIN {db_prefix}{raw:statusTable} AS {raw:statusTable} ON {raw:compare}
				LEFT JOIN {db_prefix}{raw:likeJoin}
			WHERE {raw:columnName} IN({array_int:profileIds})',
			$queryParams
		);

		return $this->prepareData($request, true);
	}

	public function getByStatus(array $statusIds): array
	{
		$queryParams = array_merge($this->getDefaultQueryParamsWithLikes(LikeEntity::TYPE_COMMENT), [
			'columnName' => CommentEntity::STATUS_ID,
			'statusIds' => $statusIds,
		]);

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:from}
				JOIN {db_prefix}{raw:likeJoin}
			WHERE {raw:columnName} IN({array_int:statusIds})',
			$queryParams
		);

		return $this->prepareData($request, true);
	}

	public function getByIds(array $commentIds = []): ?array
	{
		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:from}
				LEFT JOIN {db_prefix}{raw:likeJoin}
			WHERE {raw:columnName} IN ({array_int:ids})
			LIMIT {int:limit}',
			array_merge($this->getDefaultQueryParamsWithLikes(LikeEntity::TYPE_COMMENT), [
				'limit' => 1,
				'ids' => array_map('intval', $commentIds),
				'columnName' => self::PARENT_LIKE_IDENTIFIER . '.' . CommentEntity::ID,
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
		return CommentEntity::ID;
	}

	public function getColumns(): array
	{
		return CommentEntity::getColumns();
	}

	public function getAliasedColumns(): array
	{
		return array_map(function ($columnName): string {
			return sprintf(BaseEntity::ALIAS_ID, CommentEntity::TABLE, $columnName);
		}, CommentEntity::getColumns());
	}

	private function prepareData($request, bool $useStatusID = false): array
	{
		$comments = [];
		$usersIds = [];

		while ($row = $this->dbClient->fetchAssoc($request)) {
			if ($useStatusID) {
				$comments[$row[CommentEntity::STATUS_ID]][$row[CommentEntity::ID]] =
					array_map(function ($rowValue) {
						return ctype_digit($rowValue) ? ((int)$rowValue) : $rowValue;
					}, $row);
			} else {
				$comments[$row[CommentEntity::ID]] = array_map(function ($rowValue) {
					return ctype_digit($rowValue) ? ((int)$rowValue) : $rowValue;
				}, $row);
			}

			$usersIds[] = (int)$row[CommentEntity::USER_ID];
		}

		$this->dbClient->freeResult($request);

		return [
			'data' => $comments,
			'usersIds' => array_unique($usersIds),
		];
	}
}
