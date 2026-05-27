<?php

declare(strict_types=1);


namespace Breeze\Service;

interface PermissionsServiceInterface
{
	public const IDENTIFIER = 'Permissions';

	public function hookPermissions(&$permissionGroups, &$permissionList): void;

	public function permissions(int $profileOwner = 0, int $userPoster = 0): array;

	public function isFeatureEnable(): array;

	public function canViewActivity(int $viewerId): bool;

	/**
	 * Returns true when the viewer may see content on a profile wall.
	 * Uses profile_view (a standard SMF permission) rather than the
	 * custom VIEW_GENERAL_WALL permission, which only gates the feed.
	 */
	public function canViewProfileWall(int $viewerId): bool;
}
