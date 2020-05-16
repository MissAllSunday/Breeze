<?php

declare(strict_types=1);


namespace Breeze\Util;

class Permissions
{
	public const DELETE_COMMENTS = 'deleteComments';
	public const DELETE_OWN_COMMENTS = 'deleteOwnComments';
	public const DELETE_PROFILE_COMMENTS = 'deleteProfileComments';
	public const DELETE_STATUS = 'deleteStatus';
	public const DELETE_OWN_STATUS = 'deleteOwnStatus';
	public const DELETE_PROFILE_STATUS = 'deleteProfileStatus';
	public const POST_STATUS = 'postStatus';
	public const POST_COMMENTS = 'postComments';
	public const USE_MOOD = 'useMood';

	public const ALL_PERMISSIONS = [
		self::DELETE_COMMENTS,
		self::DELETE_OWN_COMMENTS,
		self::DELETE_PROFILE_COMMENTS,
		self::DELETE_STATUS,
		self::DELETE_STATUS,
		self::DELETE_OWN_STATUS,
		self::DELETE_PROFILE_STATUS,
		self::POST_STATUS,
		self::POST_COMMENTS,
		self::USE_MOOD,
	];

	public static function isNotGuest(string $errorTextKey): void
	{
		is_not_guest($errorTextKey);
	}

	public static function isAllowedTo(string $permissionName): bool
	{
		return allowedTo($permissionName);
	}
}
