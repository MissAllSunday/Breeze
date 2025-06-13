<?php

declare(strict_types=1);


namespace Breeze\Repository;

interface BaseRepositoryInterface
{
	public const int TTL = 360;

	public function handleLikes($type, $content): array;

	public function getUsersToLoad(array $userIds = []): array;

	public function loadUsersInfo(array $userIds = []): array;

	public function getCurrentUserInfo(): array;
}
