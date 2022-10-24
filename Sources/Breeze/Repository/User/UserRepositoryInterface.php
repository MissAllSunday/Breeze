<?php

declare(strict_types=1);

namespace Breeze\Repository\User;

interface UserRepositoryInterface
{
	public function getById(int $id): array;

	public function save(array $userSettings, int $userId): bool;
}
