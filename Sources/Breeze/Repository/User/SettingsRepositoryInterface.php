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
}
