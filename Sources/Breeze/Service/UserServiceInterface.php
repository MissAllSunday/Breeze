<?php

declare(strict_types=1);

namespace Breeze\Service;

interface UserServiceInterface extends BaseServiceInterface
{
	public function getCurrentUserInfo(): array;

	public function getCurrentUserSettings(): array;

	public function getUserSettings(int $userId): array;

	public function hookProfilePopUp(&$profile_items): void;

	public function hookAlertsPref(array &$alertTypes): void;

	public function stalkingCheck(int $userStalkedId = 0): bool;
}
