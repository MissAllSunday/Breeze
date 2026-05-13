<?php

declare(strict_types=1);

namespace Breeze\Repository;

use Breeze\Enums\BuddyStatus;

interface BuddyRequestRepositoryInterface
{
	public function insert(int $senderId, int $receiverId): void;

	public function delete(int $senderId, int $receiverId): void;

	public function updateStatus(int $senderId, int $receiverId, int $status): void;

	public function getPendingByReceiver(int $receiverId): array;

	public function getStatus(int $senderId, int $receiverId): ?int;

	/**
	 * @return array<int, BuddyStatus>
	 */
	public function getStatusesForUsers(int $currentUserId, array $userIds): array;
}
