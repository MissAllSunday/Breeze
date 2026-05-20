<?php

declare(strict_types=1);

namespace Breeze\Repository\User;

use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\BaseRepositoryInterface;

interface SettingsRepositoryInterface extends BaseRepositoryInterface
{
	public function getById(int $id): UserSettingsEntity;

	/**
	 * @return array<int, UserSettingsEntity>
	 */
	public function getByIds(array $ids): array;

	public function insert(array $userSettings, int $userId): bool;

	/**
	 * Invalidate stale caches after a block-list change: user settings cache
	 * and the viewer's buddy-activity initial-page cache.
	 */
	public function invalidateBlockListCaches(int $userId): void;

	/**
	 * Enable the personal wall for every member:
	 *   1. UPDATE existing rows where variable = 'wall' to value = '1'.
	 *   2. INSERT a wall = '1' row for any member who has no settings row yet.
	 *
	 * Idempotent — safe to call multiple times.
	 */
	public function enableAllWalls(): void;
}
