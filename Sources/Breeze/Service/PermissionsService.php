<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Entity\SettingsEntity;
use Breeze\Enums\PermissionsEnum;
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

	/**
	 * @param int $profileOwner wall owner id, 0 when there is no single owner
	 *                          (the general wall / buddy feed)
	 */
	public function permissions(int $profileOwner = 0): array
	{
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

		if ($this->isGuest()) {
			return $perm;
		}

		$isProfileOwner = $this->isProfileOwner($profileOwner);

		$perm[PermissionsEnum::TYPE_STATUS]['post'] = $this->canPost(PermissionsEnum::TYPE_STATUS, $profileOwner);
		$perm[PermissionsEnum::TYPE_COMMENTS]['post'] = $this->canPost(PermissionsEnum::TYPE_COMMENTS, $profileOwner);

		$perm[PermissionsEnum::TYPE_STATUS]['delete'] = $this->canDeleteAny(PermissionsEnum::TYPE_STATUS, $isProfileOwner);
		$perm[PermissionsEnum::TYPE_COMMENTS]['delete'] = $this->canDeleteAny(PermissionsEnum::TYPE_COMMENTS, $isProfileOwner);

		return $perm;
	}

	/**
	 * Authorize deletion of one concrete item.
	 *
	 * Both ids MUST come from persisted rows (status/comment `user_id` and the
	 * parent status `wall_id`), never from the request payload: trusting a
	 * client-supplied author id lets any member with `deleteOwn*` delete
	 * anyone's content by echoing their own id back.
	 *
	 * @param string $type PermissionsEnum::TYPE_STATUS|TYPE_COMMENTS
	 * @param int $authorId  item author, 0 when unknown
	 * @param int $wallOwnerId owner of the wall the item lives on, 0 when unknown
	 */
	public function canDelete(string $type, int $authorId, int $wallOwnerId): bool
	{
		if ($this->isGuest()) {
			return false;
		}

		$viewerId = $this->viewerId();

		// The viewer authored the item.
		if ($authorId !== 0
			&& $authorId === $viewerId
			&& $this->isAllowedTo(PermissionsEnum::getDeletePermission($type, PermissionsEnum::OWN))) {
			return true;
		}

		// The item sits on the viewer's own wall.
		if ($wallOwnerId !== 0
			&& $wallOwnerId === $viewerId
			&& $this->isAllowedTo(PermissionsEnum::getDeletePermission($type, PermissionsEnum::PROFILE))) {
			return true;
		}

		// Neither: requires the blanket (mod/admin) permission.
		return $this->isAllowedTo(PermissionsEnum::getDeletePermission($type));
	}

	/**
	 * Authorize creating one concrete item on a given wall.
	 *
	 * @param string $type PermissionsEnum::TYPE_STATUS|TYPE_COMMENTS
	 * @param int $wallOwnerId owner of the target wall, 0 for the general wall
	 */
	public function canPost(string $type, int $wallOwnerId): bool
	{
		if ($this->isGuest()) {
			return false;
		}

		// Posting on your own wall is always allowed.
		if ($this->isProfileOwner($wallOwnerId)) {
			return true;
		}

		return $this->isAllowedTo(
			$type === PermissionsEnum::TYPE_STATUS
				? PermissionsEnum::POST_STATUS
				: PermissionsEnum::POST_COMMENTS
		);
	}

	public function isFeatureEnable(): array
	{
		return [
			'enableLikes' => (bool) $this->modSetting(SettingsEntity::ENABLE_LIKES),
		];
	}

	public function forumPermissions(): array
	{
		$isEnable = [];

		foreach (PermissionsEnum::ALL_FORUM as $forumPermission) {
			$isEnable[$this->snakeToCamel($forumPermission)] = $this->isAllowedTo($forumPermission);
		}

		return $isEnable;
	}

	public function canViewActivity(int $viewerId): bool
	{
		return $this->isAllowedTo(PermissionsEnum::VIEW_GENERAL_WALL);
	}

	public function canViewProfileWall(int $viewerId): bool
	{
		return $this->isAllowedTo(PermissionsEnum::PROFILE_VIEW);
	}

	/**
	 * Wall-level "may delete anything here" flag: profile-owner rights or the
	 * blanket permission. Own-content rights are per item, see canDelete().
	 */
	protected function canDeleteAny(string $type, bool $isProfileOwner): bool
	{
		if ($isProfileOwner && $this->isAllowedTo(PermissionsEnum::getDeletePermission($type, PermissionsEnum::PROFILE))) {
			return true;
		}

		return $this->isAllowedTo(PermissionsEnum::getDeletePermission($type));
	}

	protected function isProfileOwner(int $profileOwner): bool
	{
		// 0 means "no single owner" (general wall), never a match.
		return $profileOwner !== 0 && $profileOwner === $this->viewerId();
	}

	protected function viewerId(): int
	{
		$user_info = $this->global('user_info');

		return (int) ($user_info['id'] ?? 0);
	}

	protected function isGuest(): bool
	{
		$user_info = $this->global('user_info');

		return !empty($user_info['is_guest']) || $this->viewerId() === 0;
	}
}
