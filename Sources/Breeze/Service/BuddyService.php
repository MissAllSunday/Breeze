<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Entity\BuddyRequestEntity;
use Breeze\Entity\SettingsEntity;
use Breeze\Enums\BuddyStatus;
use Breeze\Repository\BuddyRequestRepositoryInterface;
use Breeze\Repository\User\SettingsRepositoryInterface as UserSettingsRepository;
use Breeze\Traits\TextTrait;

class BuddyService implements BuddyServiceInterface
{
	use TextTrait;

	protected const string CONTENT_TYPE = Breeze::PATTERN . 'buddy';
	protected const string CONTENT_ACTION_INVITE = Breeze::PATTERN . 'invite';
	protected const string CONTENT_ACTION_CONFIRMED = Breeze::PATTERN . 'confirmed';

	public function __construct(
		protected UserSettingsRepository $userSettingsRepository,
		protected AlertServiceInterface $alertService,
		protected BuddyRequestRepositoryInterface $buddyRequestRepository,
	) {
	}

	public function addBuddy(int $receiverId, array $currentUserInfo): void
	{
		$sentRequests = $this->buddyRequestRepository->getBy(
			BuddyRequestEntity::SENDER_ID,
			[$currentUserInfo['id']]
		);
		$receivedRequests = $this->buddyRequestRepository->getBy(
			BuddyRequestEntity::RECEIVER_ID,
			[$currentUserInfo['id']]
		);

		foreach (array_merge($sentRequests, $receivedRequests) as $request) {
			if (
				($request->getSenderId() === $currentUserInfo['id'] && $request->getReceiverId() === $receiverId) ||
				($request->getReceiverId() === $currentUserInfo['id'] && $request->getSenderId() === $receiverId)
			) {
				return;
			}
		}

		$this->buddyRequestRepository->insert($currentUserInfo['id'], $receiverId);

		$this->alertService->send(AlertEntity::from([
			AlertEntity::ID_MEMBER => $receiverId,
			AlertEntity::ID_MEMBER_STARTED => $currentUserInfo['id'],
			AlertEntity::CONTENT_TYPE => self::CONTENT_TYPE,
			AlertEntity::CONTENT_ID => $currentUserInfo['id'],
			AlertEntity::CONTENT_ACTION => self::CONTENT_ACTION_INVITE,
		]));
	}

	public function removeBuddy(int $receiverId, array $currentUserInfo): void
	{
		$this->buddyRequestRepository->deleteByUsers($currentUserInfo['id'], $receiverId);
		$this->buddyRequestRepository->deleteByUsers($receiverId, $currentUserInfo['id']);

		$newBuddiesList = array_diff($currentUserInfo['buddies'], [$receiverId]);

		updateMemberData($currentUserInfo['id'], [
			'buddies' => implode(',', $newBuddiesList),
		]);
	}

	public function confirmBuddy(int $senderId, array $currentUserInfo): void
	{
		$this->buddyRequestRepository->updateStatus(
			$senderId,
			$currentUserInfo['id'],
			BuddyStatus::Confirmed->toDbStatus()
		);

		// Add the original sender to the current user's buddy list
		updateMemberData($currentUserInfo['id'], [
			'buddies' => implode(',', array_merge($currentUserInfo['buddies'], [$senderId])),
		]);

		// If admin allows auto follow back and user has it enabled, add bidirectionally
		if ($this->isEnable(SettingsEntity::ALLOW_AUTO_FOLLOW_BACK)) {
			$currentUserSettings = $this->userSettingsRepository->getById($currentUserInfo['id']);

			if ($currentUserSettings->getAutoFollowBack() === 1) {
				$senderSettings = $this->userSettingsRepository->getById($senderId);
				$senderBuddies = $senderSettings->getBuddies();

				if (!in_array($currentUserInfo['id'], $senderBuddies)) {
					updateMemberData($senderId, [
						'buddies' => implode(',', array_merge($senderBuddies, [$currentUserInfo['id']])),
					]);
				}
			}
		}

		// Notify the original sender that their invite was accepted
		$this->alertService->send(AlertEntity::from([
			AlertEntity::ID_MEMBER => $senderId,
			AlertEntity::ID_MEMBER_STARTED => $currentUserInfo['id'],
			AlertEntity::CONTENT_TYPE => self::CONTENT_TYPE,
			AlertEntity::CONTENT_ID => $currentUserInfo['id'],
			AlertEntity::CONTENT_ACTION => self::CONTENT_ACTION_CONFIRMED,
		]));
	}

	public function getPendingRequests(int $userId): array
	{
		$pendingRequests = $this->buddyRequestRepository->getStatusBy(
			BuddyRequestEntity::PENDING,
			BuddyRequestEntity::RECEIVER_ID,
			[$userId]
		);

		if (empty($pendingRequests)) {
			return [];
		}

		$senderIds = array_map(fn (BuddyRequestEntity $request) => $request->getSenderId(), $pendingRequests);
		$senderData = $this->buddyRequestRepository->loadUsersInfo($senderIds);

		return array_map(function (BuddyRequestEntity $request) use ($senderData) {
			$senderId = $request->getSenderId();

			return [
				'request' => $request->jsonSerialize(),
				'sender' => $senderData[$senderId] ?? [],
			];
		}, $pendingRequests);
	}

	public function declineBuddyRequest(int $senderId, int $receiverId): void
	{
		$this->buddyRequestRepository->deleteByUsers($senderId, $receiverId);
	}

	public function getBuddyStatusForUsers(int $currentUserId, array $userIds): array
	{
		if ($userIds === []) {
			return [];
		}

		$sent = $this->buddyRequestRepository->getBy(
			BuddyRequestEntity::SENDER_ID,
			[$currentUserId]
		);
		$received = $this->buddyRequestRepository->getBy(
			BuddyRequestEntity::RECEIVER_ID,
			[$currentUserId]
		);

		$results = [];
		foreach (array_merge($sent, $received) as $request) {
			$otherUserId = $request->getSenderId() === $currentUserId
				? $request->getReceiverId()
				: $request->getSenderId();

			if (in_array($otherUserId, $userIds, true)) {
				$results[$otherUserId] = BuddyStatus::fromDbStatus($request->getStatus());
			}
		}

		return $results;
	}
}
