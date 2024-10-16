<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\PermissionsEnum;
use Breeze\Traits\PermissionsTrait;
use Breeze\Traits\SettingsTrait;
use Breeze\Traits\TextTrait;

class PermissionsService implements PermissionsServiceInterface
{
	use TextTrait;
	use PermissionsTrait;
	use SettingsTrait;

	public function hookPermissions(&$permissionGroups, &$permissionList): void
	{
		$this->setLanguage(Breeze::NAME . self::IDENTIFIER);

		$permissionGroups['membergroup']['simple'] = ['breeze_per_simple'];
		$permissionGroups['membergroup']['classic'] = ['breeze_per_classic'];

		foreach (PermissionsEnum::ALL_PERMISSIONS as $permissionName) {
			$permissionList['membergroup']['breeze_' . $permissionName] = [
				false,
				'breeze_per_classic',
				'breeze_per_simple',];
		}
	}

	public function permissions(int $profileOwner = 0, int $userPoster = 0): array
	{
		$user_info = $this->global('user_info');

		$perm = [
			PermissionsEnum::TYPE_STATUS =>  [
				'edit' => false,
				'delete' => false,
				'post' => false,
			],
			PermissionsEnum::TYPE_COMMENTS =>  [
				'edit' => false,
				'delete' => false,
				'post' => false,
			],
			PermissionsEnum::IS_ENABLE => $this->isFeatureEnable(),
			PermissionsEnum::FORUM => $this->forumPermissions(),
		];

		// NO! you don't have permission to do nothing...
		if ($user_info['is_guest'] || !$userPoster || !$profileOwner) {
			return $perm;
		}

		// Profile owner?
		$isProfileOwner = $profileOwner === (int) $user_info['id'];

		// Status owner?
		$isPosterOwner = $userPoster === (int) $user_info['id'];

		// Lets check the posing bit first. Profile owner can always post.
		if ($isProfileOwner) {
			$perm[PermissionsEnum::TYPE_STATUS]['post'] = true;
			$perm[PermissionsEnum::TYPE_COMMENTS]['post'] = true;
		} else {
			$perm[PermissionsEnum::TYPE_STATUS]['post'] = $this->isAllowedTo(PermissionsEnum::POST_STATUS);
			$perm[PermissionsEnum::TYPE_COMMENTS]['post'] =  $this->isAllowedTo(PermissionsEnum::POST_COMMENTS);
		}

		$perm[PermissionsEnum::TYPE_STATUS]['delete'] = $this->handleDelete(PermissionsEnum::TYPE_STATUS, $isPosterOwner, $isProfileOwner);
		$perm[PermissionsEnum::TYPE_COMMENTS]['delete'] =  $this->handleDelete(PermissionsEnum::TYPE_STATUS, $isPosterOwner, $isProfileOwner);

		return $perm;
	}

	public function isFeatureEnable(): array
	{
		$isEnable = [];

		foreach (PermissionsEnum::ALL_FEATS as $featureName) {
			$isEnable[$this->snakeToCamel($featureName)] = $this->modSetting($featureName);
		}

		return $isEnable;
	}

	public function forumPermissions(): array
	{
		$isEnable = [];

		foreach (PermissionsEnum::ALL_FORUM as $forumPermission) {
			$isEnable[$this->snakeToCamel($forumPermission)] = $this->isAllowedTo($forumPermission);
		}

		return $isEnable;
	}

	protected function handleDelete(string $type, bool $isPosterOwner, $isProfileOwner) : bool
	{
		// It all starts with an empty vessel...
		$allowed = [];

		// Your own data?
		if ($isPosterOwner && $this->isAllowedTo(PermissionsEnum::getDeletePermission($type, PermissionsEnum::OWN))) {
			$allowed[] = 1;
		}

		// Nope? then is this your own profile?
		if ($isProfileOwner && $this->isAllowedTo(PermissionsEnum::getDeletePermission($type, PermissionsEnum::PROFILE))) {
			$allowed[] = 1;
		}

		// No poster and no profile owner, must be an admin/mod or something.
		if ($this->isAllowedTo(PermissionsEnum::getDeletePermission($type))) {
			$allowed[] = 1;
		}

		return in_array(1, $allowed, true);
	}
}
