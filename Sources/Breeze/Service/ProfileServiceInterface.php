<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\UserSettingsEntity;

interface ProfileServiceInterface
{
	public function getCurrentUserInfo(): array;

	public function getCurrentUserSettings(): UserSettingsEntity;

	public function getUserSettings(int $userId): UserSettingsEntity;

	public function hookProfilePopUp(&$profile_items): void;

	public function hookAlertsPref(array &$alertTypes): void;

	public function isAllowedToSeePage(UserSettingsEntity $profileSettings, int $profileId = 0, int $userId = 0): bool;

	public function loadComponents(int $profileId = 0): void;

	public function loadUsersInfo(array $userIds = []): array;

	public function updateMemberData(int $userId, array $updatedData): void;

	public function setEditor(): void;
}
