<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\PermissionsEnum;
use Breeze\Traits\PermissionsTrait;
use Breeze\Traits\SettingsTrait;

class ValidateService
{
	use SettingsTrait;
	use PermissionsTrait;

	public function permissions(string $type, int $profileOwner = 0, int $userPoster = 0): array
	{
		$user_info = $this->global('user_info');

		$perm = [
			'edit' => false,
			'delete' => false,
			'post' => false,
			'postComments' => false,
		];

		// NO! you don't have permission to do nothing...
		if ($user_info['is_guest'] || !$userPoster || !$profileOwner || empty($type)) {
			return $perm;
		}

		// Profile owner?
		$isProfileOwner = $profileOwner === (int) $user_info['id'];

		// Status owner?
		$isPosterOwner = $userPoster === (int) $user_info['id'];

		// Lets check the posing bit first. Profile owner can always post.
		if ($isProfileOwner) {
			$perm['post'] = true;
			$perm['postComments'] = true;
		} else {
			$perm['post'] = $this->isAllowedTo(PermissionsEnum::POST_STATUS);
			$perm['postComments'] = $this->isAllowedTo(PermissionsEnum::POST_COMMENTS);
		}

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

		$perm['delete'] = in_array(1, $allowed, true);

		return $perm;
	}
}
