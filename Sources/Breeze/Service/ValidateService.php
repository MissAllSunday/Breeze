<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Util\Permissions;

class ValidateService extends BaseService implements ServiceInterface
{
	public function permissions($type, int $profileOwner = 0, int $userPoster = 0): array
	{
		$user_info = $this->global('user_info');

		$perm = [
			'edit' => false,
			'delete' => '',
			'post' => false,
			'postComments' => false,
		];

		// NO! you don't have permission to do nothing...
		if ($user_info['is_guest'] || !$userPoster || !$profileOwner || empty($type)) {
			return $perm;
		}

		// Profile owner?
		$isProfileOwner = $profileOwner == $user_info['id'];

		// Status owner?
		$isPosterOwner = $userPoster == $user_info['id'];

		// Lets check the posing bit first. Profile owner can always post.
		if ($isProfileOwner) {
			$perm['post'] = true;
			$perm['postComments'] = true;
		} else {
			$perm['post'] = Permissions::isAllowedTo('breeze_post' . $type);
			$perm['postComments'] = Permissions::isAllowedTo('breeze_postComments');
		}

		// It all starts with an empty vessel...
		$allowed = [];

		// Your own data?
		if ($isPosterOwner && Permissions::isAllowedTo('breeze_deleteOwn' . $type)) {
			$allowed[] = 1;
		}

		// Nope? then is this your own profile?
		if ($isProfileOwner && Permissions::isAllowedTo('breeze_deleteProfile' . $type)) {
			$allowed[] = 1;
		}

		// No poster and no profile owner, must be an admin/mod or something.
		if (Permissions::isAllowedTo('breeze_delete' . $type)) {
			$allowed[] = 1;
		}

		$perm['delete'] = in_array(1, $allowed);

		return $perm;
	}
}
