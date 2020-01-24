<?php


namespace Breeze\Service;


class Permissions
{
	public function permissions($type, $profileOwner = false, $userPoster = false)
	{
		global $user_info;

		// Add this bit here to make it easier in the future to add more permissions.
		$perm = [
			'edit' => false,
			'delete' => '',
			'post' => false,
			'postComments' => false,
		];

		// NO! you don't have permission to do nothing...
		if ($user_info['is_guest'] || !$userPoster || !$profileOwner || empty($type))
			return $perm;

		// Profile owner?
		$isProfileOwner = $profileOwner == $user_info['id'];

		// Status owner?
		$isPosterOwner = $userPoster == $user_info['id'];

		// Lets check the posing bit first. Profile owner can always post.
		if ($isProfileOwner)
		{
			$perm['post'] = true;
			$perm['postComments'] = true;
		}

		else
		{
			$perm['post'] = allowedTo('breeze_post' . $type);
			$perm['postComments'] = allowedTo('breeze_postComments');
		}

		// It all starts with an empty vessel...
		$allowed = [];

		// Your own data?
		if ($isPosterOwner && allowedTo('breeze_deleteOwn' . $type))
			$allowed[] = 1;

		// Nope? then is this your own profile?
		if ($isProfileOwner && allowedTo('breeze_deleteProfile' . $type))
			$allowed[] = 1;

		// No poster and no profile owner, must be an admin/mod or something.
		if (allowedTo('breeze_delete' . $type))
			$allowed[] = 1;

		$perm['delete'] = in_array(1, $allowed);

		return $perm;
	}

}