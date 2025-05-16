<?php

declare(strict_types=1);

namespace Breeze\Repository\User;

use Breeze\Repository\BaseRepositoryInterface;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
	public function getById(int $id): array;

	public function save(array $userSettings, int $userId): bool;
}
