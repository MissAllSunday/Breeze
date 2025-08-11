<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\EntityInterface;

interface BaseRepositoryInterface
{
	public const int TTL = 360;

	public function handleLikes($type, $content): array;

	public function getUsersToLoad(array $userIds = []): array;

	public function loadUsersInfo(array $userIds = []): array;

	public function getCurrentUserInfo(): array;

	public function getById(int $id): ?EntityInterface;

	public function doesContentExists(int $id): bool;
}
