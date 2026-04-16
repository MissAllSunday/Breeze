<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\StatusEntity;
use Breeze\Util\Validate\DataNotFoundException;

interface StatusRepositoryInterface extends BaseRepositoryInterface
{
	/**
	 * @throws InvalidStatusException
	 * @return array [StatusEntity]
	 */
	public function insert(StatusEntity $statusEntity): array;

	public function getByProfile(array $userProfiles = [], int $maxIndex = 0, ?string $cursor = null): array;

	public function getBy(string $columnName, array $data = [], int $maxIndex = 0, ?string $cursor = null): array;

	/**
	 * Encode a cursor for pagination
	 */
	public function encodeCursor(int $id, int $createdAt): string;

	/**
	 * Decode a cursor for pagination
	 */
	public function decodeCursor(string $cursor): ?array;

	/**
	 * Generate next cursor from status entities
	 */
	public function getNextCursor(array $statuses): ?string;

	/**
	 * @throws DataNotFoundException
	 */
	public function getById(int $id = 0): StatusEntity;

	/**
	 * @throws InvalidStatusException
	 */
	public function deleteById(int $statusId): bool;

	public function getCount(array $queryParams = []): int;

	public function getBasicInfoById(int $id): StatusEntity;

	public function recountComments(): void;

	public function recountLikes(): void;
}
