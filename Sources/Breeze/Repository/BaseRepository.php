<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Database\ClientInterface;
use Breeze\Entity\EntityInterface;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\MemberEntity;
use Breeze\Entity\SharedEntity;
use Breeze\Enums\LikesEnum;
use Breeze\Service\ProfileService;
use Breeze\Traits\CacheTrait;
use Breeze\Traits\PermissionsTrait;
use Breeze\Traits\TextTrait;
use InvalidArgumentException;

abstract class BaseRepository implements BaseRepositoryInterface
{
	use PermissionsTrait;
	use CacheTrait;
	use TextTrait;

	protected const string PARENT_LIKE_IDENTIFIER = 'parent';
	protected const string LIKE_IDENTIFIER = 'likes';

	public function __construct(
		protected readonly ClientInterface $dbClient,
		protected readonly LikeRepositoryInterface | null $likeRepository = null
	) {}

	protected function buildSetUpdate(EntityInterface $entity): string
	{
		$set = 'SET ';
		$columns = $entity->getColumns();
		foreach (array_keys($entity->toArray()) as $name) {
			$set .= ' ' . $name . ' = {' . $columns[$name] . ':' . $name . '},';
		}

		return rtrim($set, ',');
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function doesContentExists(int $id): bool
	{
		$columnName = $this->getColumnId();

		if (!$this->isValidColumn($columnName)) {
			$this->logMessage(sprintf(
				'%s::doesContentExists() got an unknown id column %s',
				static::class,
				var_export($columnName, true)
			));

			throw new InvalidArgumentException($this->getText('error_internal'));
		}

		$result = $this->dbClient->query(
			'
			SELECT 1
			FROM {db_prefix}{raw:from}
			WHERE {raw:columnName} = {int:id}
			LIMIT 1',
			array_merge($this->getDefaultQueryParams(), [
				'columnName' => self::PARENT_LIKE_IDENTIFIER . '.' . $columnName,
				'id' => $id,
			])
		);

		$exists = $this->dbClient->numRows($result) > 0;

		$this->dbClient->freeResult($result);

		return $exists;
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
		$userIds = array_unique($userIds);
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
			$loadedUsers[$userId]['legacy_url'] = sprintf(ProfileService::LEGACY_URL, $userId);
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
							LikeEntity::TYPE => 'COALESCE(' . $columnName . ', "' . $type->value . '")',
							LikeEntity::ID_MEMBER => 'COALESCE(' . $columnName . ', 0)',
							default => $columnName,
						} . ' AS ' . LikeEntity::IDENTIFIER . $likeColumn;
				}, LikeEntity::getColumns())),
			'tableName' => $this->getTableName(),
			'from' => $this->getTableName() . ' AS ' . self::PARENT_LIKE_IDENTIFIER,
			'likeJoin' => LikeEntity::TABLE . ' AS ' . self::LIKE_IDENTIFIER . '
			 	ON (' . self::LIKE_IDENTIFIER . '.' . LikeEntity::ID . ' =
			 	 ' . self::PARENT_LIKE_IDENTIFIER . '.' . $parentIdentifier . '
			 	AND ' . self::LIKE_IDENTIFIER . '.' . LikeEntity::TYPE . ' = "' . $type->value . '")',
		];
	}

	/**
	 * Count rows matching a column against a set of ids.
	 *
	 * The predicate is mandatory. It used to be conditional on the caller
	 * passing a valid `columnName`, which meant a caller using the wrong array
	 * shape silently got an unfiltered full-table count instead of an error.
	 * Both `columnName` and `ids` are now required and validated up front.
	 *
	 * Uses COUNT(*) rather than selecting the id column and calling numRows():
	 * the answer is a single integer, so materialising and transferring every
	 * matching row just to count them is pure waste. CommentRepository::
	 * countOrphans() is the in-repo precedent for this shape.
	 *
	 * As in doesContentExists(), the exception message is a language string:
	 * the column name is logged, never thrown.
	 *
	 * @param array{columnName?: string, ids?: array<int, int>} $queryParams
	 * @throws InvalidArgumentException when the filter is missing, names an
	 *                                   unknown column, or has no ids
	 */
	public function getCount(array $queryParams = []): int
	{
		$columnName = $queryParams['columnName'] ?? null;

		if (!is_string($columnName) || !$this->isValidColumn($columnName)) {
			$this->logMessage(sprintf(
				'%s::getCount() got an invalid "columnName" %s',
				static::class,
				var_export($columnName, true)
			));

			throw new InvalidArgumentException($this->getText('error_internal'));
		}

		$ids = $queryParams['ids'] ?? [];

		if ($ids === []) {
			$this->logMessage(sprintf(
				'%s::getCount() got an empty "ids" array, which would emit invalid SQL.',
				static::class
			));

			throw new InvalidArgumentException($this->getText('error_internal'));
		}

		$result = $this->dbClient->query(
			'
			SELECT COUNT(*)
			FROM {db_prefix}{raw:from}
			WHERE {raw:columnName} IN ({array_int:ids})',
			array_merge($this->getDefaultQueryParams(), [
				'columnName' => $columnName,
				'ids' => array_map('intval', $ids),
			])
		);

		$row = $this->dbClient->fetchRow($result);

		$this->dbClient->freeResult($result);

		return (int) ($row[0] ?? 0);
	}

	public function isValidColumn(string $columnName): bool
	{
		return in_array($columnName, $this->getColumns());
	}

	abstract public function getTableName(): string;

	abstract public function getColumnId(): string;

	abstract public function getColumns(): array;

	abstract public function getColumnPosterId(): string;

	/**
	 * @param array $entities [EntityInterface]
	 * @return array [EntityInterface]
	 */
	protected function setLikes(array $entities, LikesEnum $type): array
	{
		// Return early if there is no data to work with
		if ($entities === []) {
			return [];
		}

		$contentIds = array_column($entities, SharedEntity::ID);

		$likesInfo = $this->likeRepository->getByContent(
			$type,
			$contentIds
		);

		array_walk($entities, function ($entity) use ($likesInfo): void {
			$id = $entity->getId();
			$entity->setLikesInfo($likesInfo[$id] ?? null);
		});

		return $entities;
	}

	protected function getDefaultQueryParams(): array
	{
		$this->getTableName();

		return [
			'from' => $this->getTableName() . ' AS ' . self::PARENT_LIKE_IDENTIFIER,
			'columns' => implode(', ', array_map(function (string $column): string {
				return self::PARENT_LIKE_IDENTIFIER . '.' . $column;
			}, $this->getColumns())),
			'tableName' => $this->getTableName(),
		];
	}

	protected function trimUserData(array $loadedUsers): array
	{
		return array_intersect_key($loadedUsers, array_flip(MemberEntity::getUserDataColumns()));
	}
}
