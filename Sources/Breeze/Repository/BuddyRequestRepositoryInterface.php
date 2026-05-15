<?php

declare(strict_types=1);

namespace Breeze\Repository;

use Breeze\Entity\BuddyRequestEntity;

interface BuddyRequestRepositoryInterface extends BaseRepositoryInterface
{
	public function insert(int $senderId, int $receiverId): void;

	public function deleteByUsers(int $senderId, int $receiverId): void;

	public function updateStatus(int $senderId, int $receiverId, int $status): void;

	/**
	 * @return BuddyRequestEntity[]
	 */
	public function getBy(string $columnName, array $data = []): array;

	/**
	 * @return BuddyRequestEntity[]
	 */
	public function getStatusBy(int $status, string $columnName = '', array $data = []): array;
}
