<?php

declare(strict_types=1);

namespace Breeze\Repository\User;

use Breeze\Entity\UserSettingsHandledEntity;
use Breeze\Repository\BaseRepositoryInterface;

interface SettingsRepositoryInterface extends BaseRepositoryInterface
{
	public function getById(int $id): UserSettingsHandledEntity;

	public function insert(array $userSettings, int $userId): bool;
}
