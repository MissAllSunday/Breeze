<?php

declare(strict_types=1);

namespace Breeze;

enum PermissionsEnum
{
	public const ADMIN_FORUM = 'admin_forum';
	public const DELETE_COMMENTS = 'deleteComments';
	public const DELETE_OWN_COMMENTS = 'deleteOwnComments';
	public const DELETE_PROFILE_COMMENTS = 'deleteProfileComments';
	public const DELETE_STATUS = 'deleteStatus';
	public const DELETE_OWN_STATUS = 'deleteOwnStatus';
	public const DELETE_PROFILE_STATUS = 'deleteProfileStatus';
	public const POST_STATUS = 'postStatus';
	public const POST_COMMENTS = 'postComments';
	public const LIKES_LIKE = 'likes_like';
	public const PROFILE_VIEW = 'profile_view';

	private const DELETE_TEMPLATE = 'delete%s%s';
	public const OWN = 'own';
	public const PROFILE = 'profile';

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
	];

	public static function isSMFPermission(string $permissionName): bool
	{
		return match ($permissionName) {
			self::LIKES_LIKE, self::ADMIN_FORUM, self::PROFILE_VIEW => true,
			default => false,
		};
	}

	public static function getDeletePermission(string $type, string $subType = ''): string
	{
		return sprintf(self::DELETE_TEMPLATE, ucfirst($subType), ucfirst($type));
	}
}
