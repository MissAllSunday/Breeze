<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Entity\SettingsEntity;
use Breeze\Traits\TextTrait;

class BuddyService implements BuddyServiceInterface
{
	use TextTrait;

	protected const string CONTENT_TYPE = Breeze::PATTERN . 'buddy';
	protected const string CONTENT_ACTION_INVITE = Breeze::PATTERN . 'invite';
	protected const string CONTENT_ACTION_ACCEPTED = Breeze::PATTERN . 'accepted';

	public function __construct(
		protected ProfileServiceInterface $profileService,
		protected AlertServiceInterface $alertService
	) {
	}

	public function addBuddy(int $receiverId, array $currentUserInfo): void
	{
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
		$newBuddiesList = array_diff($currentUserInfo['buddies'], [$receiverId]);

		$this->profileService->updateMemberData($currentUserInfo['id'], [
			'buddies' => implode(',', $newBuddiesList),
		]);
	}

	public function confirmBuddy(int $senderId, array $currentUserInfo): void
	{
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
		$alerts = $this->alertService->getPendingBuddyAlerts($userId);

		if ($alerts === []) {
			return [];
		}

		$senderIds = array_map(fn ($alert) => $alert->getIdMemberStarted(), $alerts);
		$senderData = $this->profileService->loadUsersInfo($senderIds);

		$requests = [];
		foreach ($alerts as $alert) {
			$senderId = $alert->getIdMemberStarted();
			$requests[] = [
				'alert' => $alert->toArray(),
				'sender' => $senderData[$senderId] ?? [],
			];
		}

		return $requests;
	}

	public function declineBuddyRequest(int $alertId): void
	{
		$this->alertService->delete($alertId);
	}
}
