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
	 * @param int[] $buddyIds
	 * @param int[] $excludeIds User IDs to exclude at the SQL level (authors and
	 *   wall owners). Pre-computed by WallVisibilityService::getMutualBlockIds()
	 *   to avoid fetching rows that will be discarded by the PHP-level filter.
	 *   Defaults to [] (no exclusion), so existing callers are unaffected.
	 * @param int $viewerId When > 0, the initial page result is cached under a
	 *   viewer-keyed key so repeated loads avoid a DB round-trip. Pass 0 to
	 *   disable caching (e.g. guests or test environments).
	 */
	public function getByBuddyActivity(array $buddyIds = [], int $maxIndex = 0, ?string $cursor = null, array $excludeIds = [], int $viewerId = 0): array;

	/**
	 * Invalidate the buddy-activity initial-page cache for a specific viewer.
	 * Must be called whenever the viewer's (or a buddy's) block list changes.
	 */
	public function invalidateBuddyActivityCache(int $viewerId): void;

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
