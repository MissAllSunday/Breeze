<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Breeze;

class PermissionsService extends BaseService implements ServiceInterface
{
	public const IDENTIFIER = 'Permissions';

	public const DELETE_COMMENTS = 'deleteComments';
	public const DELETE_OWN_COMMENTS = 'deleteOwnComments';
	public const DELETE_OWN_PROFILE_COMMENTS = 'deleteProfileComments';
	public const DELETE_STATUS = 'deleteStatus';
	public const DELETE_OWN_STATUS = 'deleteOwnStatus';
	public const DELETE_OWN_PROFILE_STATUS = 'deleteProfileStatus';
	public const POST_STATUS = 'postStatus';
	public const POST_COMMENTS = 'postComments';
	public const USE_COVER = 'canCover';
	public const USE_MOOD = 'canMood';

	public const ALL_PERMISSIONS = [
		self::DELETE_COMMENTS,
		self::DELETE_OWN_COMMENTS,
		self::DELETE_OWN_PROFILE_COMMENTS,
		self::DELETE_STATUS,
		self::DELETE_OWN_STATUS,
		self::DELETE_OWN_PROFILE_STATUS,
		self::POST_STATUS,
		self::POST_COMMENTS,
		self::USE_COVER,
		self::USE_MOOD
	];

	public function hookPermissions(&$permissionGroups, &$permissionList): void
	{
		$this->setLanguage(Breeze::NAME . self::IDENTIFIER);

		$permissionGroups['membergroup']['simple'] = ['breeze_per_simple'];
		$permissionGroups['membergroup']['classic'] = ['breeze_per_classic'];

		foreach (self::ALL_PERMISSIONS as $permissionName)
			$permissionList['membergroup']['breeze_' . $permissionName] = [
				false,
				'breeze_per_classic',
				'breeze_per_simple'];
	}

	public function get(string $permissionName): bool
	{
		return allowedTo($permissionName);
	}
}
