<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\UserSettingsHandledEntity;

interface ProfileServiceInterface
{
	public function getCurrentUserInfo(): array;

	public function getCurrentUserSettings(): UserSettingsHandledEntity;

	public function getUserSettings(int $userId): UserSettingsHandledEntity;

	public function hookProfilePopUp(&$profile_items): void;

	public function hookAlertsPref(array &$alertTypes): void;

	public function isAllowedToSeePage(UserSettingsHandledEntity $profileSettings, int $profileId = 0, int $userId = 0): bool;

	public function loadComponents(int $profileId = 0): void;

	public function loadUsersInfo(array $userIds = []): array;

	public function updateMemberData(int $userId, array $updatedData): void;

	public function setEditor(): void;

	public function stalkingCheck(int $userStalkedId = 0): bool;
}
