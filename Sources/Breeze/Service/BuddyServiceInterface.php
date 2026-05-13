<?php

declare(strict_types=1);

namespace Breeze\Service;

interface BuddyServiceInterface
{
	public function addBuddy(int $receiverId, array $currentUserInfo): void;

	public function removeBuddy(int $receiverId, array $currentUserInfo): void;

	public function confirmBuddy(int $senderId, array $currentUserInfo): void;

	public function getPendingRequests(int $userId): array;

	public function declineBuddyRequest(int $senderId, int $receiverId): void;

	/**
	 * @return array<int, \Breeze\Enums\BuddyStatus>
	 */
	public function getBuddyStatusForUsers(int $currentUserId, array $userIds): array;
}
