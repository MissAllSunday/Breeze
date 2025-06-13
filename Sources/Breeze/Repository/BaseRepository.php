<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Database\ClientInterface;
use Breeze\Entity\EntityInterface;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\MemberEntity;
use Breeze\LikesEnum;
use Breeze\Traits\CacheTrait;
use Breeze\Traits\TextTrait;

abstract class BaseRepository implements BaseRepositoryInterface
{
	use CacheTrait;
	use TextTrait;

	protected const string PARENT_LIKE_IDENTIFIER = 'parent';
	protected const string LIKE_IDENTIFIER = 'likes';

	protected array $loadedUsers = [];

	public function __construct(
		protected readonly ClientInterface $dbClient,
	) {}

	protected function buildSetUpdate(EntityInterface $entity): string
	{
		$set = 'SET ';
		$columns = $entity->getColumns();
		foreach ($entity->toArray() as $name => $type) {
			$set .= ' ' . $name . ' = {' . $columns[$name] . ':' . $name . '},';
		}

		return rtrim($set, ',');
	}

	public function handleLikes($type, $content): array
	{
		return [];
	}

	public function getUsersToLoad(array $userIds = []): array
	{
		return loadMemberData($userIds);
	}

	public function loadUsersInfo(array $userIds = []): array
	{
		$loadedUsers = [];

		$modSettings = $this->global('modSettings');
		$loadedIDs = $this->getUsersToLoad($userIds);

		foreach ($userIds as $userId) {
			if (!in_array($userId, $loadedIDs)) {
				$loadedUsers[$userId] = [
					'link' => $this->getSmfText('guest_title'),
					'name' => $this->getSmfText('guest_title'),
					'avatar' => ['href' => $modSettings['avatar_url'] . '/default.png'],
				];

				continue;
			}

			$loadedUsers[$userId] = $this->trimUserData(loadMemberContext($userId, true));
		}

		return $loadedUsers;
	}

	public function getCurrentUserInfo(): array
	{
		return $this->global('user_info');
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

	protected function getDefaultQueryParamsWithLikes(LikesEnum $type, string $parentIdentifier = 'id'): array
	{
		return [
			'columns' => implode(', ', array_map(function (string $parentColumn): string {
					return self::PARENT_LIKE_IDENTIFIER . '.' . $parentColumn;
				}, $this->getColumns())) . ', ' .
				implode(', ', array_map(function (string $likeColumn) use ($type): string {

					$columnName = self::LIKE_IDENTIFIER . '.' . $likeColumn;

					return match ($likeColumn) {
							LikeEntity::COLUMN_TYPE => 'COALESCE(' . $columnName . ', "' . $type->value . '")',
							LikeEntity::COLUMN_ID_MEMBER => 'COALESCE(' . $columnName . ', 0)',
							default => $columnName,
						} . ' AS ' . LikeEntity::IDENTIFIER . $likeColumn;
				}, LikeEntity::getColumns())),
			'tableName' => $this->getTableName(),
			'from' => $this->getTableName() . ' AS ' . self::PARENT_LIKE_IDENTIFIER,
			'likeJoin' => LikeEntity::TABLE . ' AS ' . self::LIKE_IDENTIFIER . '
			 	ON (' . self::LIKE_IDENTIFIER . '.' . LikeEntity::COLUMN_ID . ' =
			 	 ' . self::PARENT_LIKE_IDENTIFIER . '.' . $parentIdentifier . '
			 	AND ' . self::LIKE_IDENTIFIER . '.' . LikeEntity::COLUMN_TYPE . ' = "' . $type->value . '")',
		];
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

	abstract public function getTableName(): string;

	abstract public function getColumnId(): string;

	abstract public function getColumns(): array;

	abstract public function getColumnPosterId(): string;

	protected function getDefaultQueryParams(): array
	{
		return [
			'columns' => implode(', ', $this->getColumns()),
			'tableName' => $this->getTableName(),
		];
	}

	protected function trimUserData(array $loadedUsers): array
	{
		return array_intersect_key($loadedUsers, array_flip(MemberEntity::getUserDataColumns()));
	}
}
