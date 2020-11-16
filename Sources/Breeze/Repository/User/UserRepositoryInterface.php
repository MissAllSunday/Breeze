<?php

declare(strict_types=1);

namespace Breeze\Repository\User;

interface UserRepositoryInterface
{
	public function getUserSettings(int $userId): array;

	public function save(array $userSettings, int $userId): int;
}
