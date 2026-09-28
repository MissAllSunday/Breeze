<?php

declare(strict_types=1);

namespace Breeze\Service;

interface PermissionsServiceInterface
{
	public const IDENTIFIER = 'Permissions';

	public function hookPermissions(&$permissionGroups, &$permissionList): void;

	/**
	 * Wall-level permission snapshot. `Status.delete` / `Comments.delete` mean
	 * "may delete any item on this wall"; per-item rights go through canDelete().
	 *
	 * @param int $profileOwner wall owner id, 0 when there is no single owner
	 */
	public function permissions(int $profileOwner = 0): array;

	/**
	 * Authorize deletion of one concrete item. Both ids MUST be read from
	 * persisted rows, never from the request payload.
	 *
	 * @param string $type PermissionsEnum::TYPE_STATUS|TYPE_COMMENTS
	 * @param int $authorId item author, 0 when unknown
	 * @param int $wallOwnerId owner of the wall the item lives on, 0 when unknown
	 */
	public function canDelete(string $type, int $authorId, int $wallOwnerId): bool;

	/**
	 * Authorize creating one concrete item on a given wall.
	 *
	 * @param string $type PermissionsEnum::TYPE_STATUS|TYPE_COMMENTS
	 * @param int $wallOwnerId owner of the target wall, 0 for the general wall
	 */
	public function canPost(string $type, int $wallOwnerId): bool;

	public function isFeatureEnable(): array;

	public function canViewActivity(int $viewerId): bool;

	/**
	 * Returns true when the viewer may see content on a profile wall.
	 * Uses profile_view (a standard SMF permission) rather than the
	 * custom VIEW_GENERAL_WALL permission, which only gates the feed.
	 */
	public function canViewProfileWall(int $viewerId): bool;
}
