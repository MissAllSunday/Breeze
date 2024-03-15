<?php

declare(strict_types=1);

namespace Breeze\Service;

interface ProfileServiceInterface
{
	public function getCurrentUserInfo(): array;

	public function getCurrentUserSettings(): array;

	public function getUserSettings(int $userId): array;

	public function hookProfilePopUp(&$profile_items): void;

	public function hookAlertsPref(array &$alertTypes): void;

	public function isAllowedToSeePage(array $profileSettings, int $profileId = 0, int $userId = 0): bool;

	public function loadComponents(int $profileId = 0): void;

	public function setEditor(): void;

	public function stalkingCheck(int $userStalkedId = 0): bool;
}
