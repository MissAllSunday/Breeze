<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Entity\BuddyRequestEntity;
use Breeze\Entity\SettingsEntity;
use Breeze\Repository\BuddyRequestRepositoryInterface;
use Breeze\Traits\TextTrait;

class BuddyService implements BuddyServiceInterface
{
	use TextTrait;

	protected const string CONTENT_TYPE = Breeze::PATTERN . 'buddy';
	protected const string CONTENT_ACTION_INVITE = Breeze::PATTERN . 'invite';
	protected const string CONTENT_ACTION_ACCEPTED = Breeze::PATTERN . 'accepted';

	public function __construct(
		protected ProfileServiceInterface $profileService,
		protected AlertServiceInterface $alertService,
		protected BuddyRequestRepositoryInterface $buddyRequestRepository,
	) {
	}

	public function addBuddy(int $receiverId, array $currentUserInfo): void
	{
		$existingStatus = $this->buddyRequestRepository->getStatus(
			$currentUserInfo['id'],
			$receiverId
		);

		if ($existingStatus !== null) {
			return;
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
		$this->buddyRequestRepository->delete($currentUserInfo['id'], $receiverId);
		$this->buddyRequestRepository->delete($receiverId, $currentUserInfo['id']);

		$newBuddiesList = array_diff($currentUserInfo['buddies'], [$receiverId]);

		$this->profileService->updateMemberData($currentUserInfo['id'], [
			'buddies' => implode(',', $newBuddiesList),
		]);
	}

	public function confirmBuddy(int $senderId, array $currentUserInfo): void
	{
		$this->buddyRequestRepository->updateStatus(
			$senderId,
			$currentUserInfo['id'],
			BuddyRequestEntity::ACCEPTED
		);

		// Add the original sender to the current user's buddy list
		$this->profileService->updateMemberData($currentUserInfo['id'], [
			'buddies' => implode(',', array_merge($currentUserInfo['buddies'], [$senderId])),
		]);

		// If admin allows auto follow back and user has it enabled, add bidirectionally
		if ($this->isEnable(SettingsEntity::ALLOW_AUTO_FOLLOW_BACK)) {
			$currentUserSettings = $this->profileService->getUserSettings($currentUserInfo['id']);

			if ($currentUserSettings->getAutoFollowBack() === 1) {
				$senderSettings = $this->profileService->getUserSettings($senderId);
				$senderBuddies = $senderSettings->getBuddies();

				if (!in_array($currentUserInfo['id'], $senderBuddies)) {
					$this->profileService->updateMemberData($senderId, [
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
			AlertEntity::CONTENT_ACTION => self::CONTENT_ACTION_ACCEPTED,
		]));
	}

	public function getPendingRequests(int $userId): array
	{
		$pendingRequests = $this->buddyRequestRepository->getPendingByReceiver($userId);

		if ($pendingRequests === []) {
			return [];
		}

		$senderIds = array_map(fn ($request) => $request->getSenderId(), $pendingRequests);
		$senderData = $this->profileService->loadUsersInfo($senderIds);

		$requests = [];
		foreach ($pendingRequests as $request) {
			$senderId = $request->getSenderId();
			$requests[] = [
				'request' => $request->jsonSerialize(),
				'sender' => $senderData[$senderId] ?? [],
			];
		}

		return $requests;
	}

	public function declineBuddyRequest(int $senderId, int $receiverId): void
	{
		$this->buddyRequestRepository->delete($senderId, $receiverId);
	}

	public function getBuddyStatusForUsers(int $currentUserId, array $userIds): array
	{
		return $this->buddyRequestRepository->getStatusesForUsers($currentUserId, $userIds);
	}
}
