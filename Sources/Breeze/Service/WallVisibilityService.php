<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\StatusEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\User\SettingsRepositoryInterface;

class WallVisibilityService implements WallVisibilityServiceInterface
{
	public function __construct(
		private readonly SettingsRepositoryInterface $userSettingsRepository,
		private readonly PermissionsServiceInterface $permissionsService,
	) {}

	public function isVisibleToViewer(int $authorId, int $wallOwnerId, int $viewerId): bool
	{
		if (!$this->permissionsService->canViewActivity($viewerId)) {
			return false;
		}

		$settings = $this->loadSettings([$authorId, $wallOwnerId, $viewerId]);

		return $this->passesAllGates($authorId, $wallOwnerId, $viewerId, $settings);
	}

	public function filterStatusesForFeed(array $statuses, int $viewerId): array
	{
		return $this->filterStatuses(
			$statuses,
			$viewerId,
			fn (StatusEntity $status, array $settings): bool => $this->passesAllGates(
				$status->getUserId(),
				$status->getWallId(),
				$viewerId,
				$settings,
			),
		);
	}

	public function filterStatusesForWall(array $statuses, int $viewerId): array
	{
		return $this->filterStatuses(
			$statuses,
			$viewerId,
			fn (StatusEntity $status, array $settings): bool => $this->passesSafetyGates(
				$status->getUserId(),
				$status->getWallId(),
				$viewerId,
				$settings,
			),
		);
	}

	/**
	 * @param array<int, StatusEntity> $statuses
	 * @param callable(StatusEntity, array<int, UserSettingsEntity>): bool $predicate
	 * @return array<int, StatusEntity>
	 */
	private function filterStatuses(array $statuses, int $viewerId, callable $predicate): array
	{
		if ($statuses === [] || !$this->permissionsService->canViewActivity($viewerId)) {
			return [];
		}

		$ids = [$viewerId];
		foreach ($statuses as $status) {
			$ids[] = $status->getUserId();
			$ids[] = $status->getWallId();
		}

		$settings = $this->loadSettings($ids);

		return array_filter(
			$statuses,
			static fn (StatusEntity $status): bool => $predicate($status, $settings),
		);
	}

	public function filterVisibleComments(array $comments, int $viewerId): array
	{
		if ($comments === [] || !$this->permissionsService->canViewActivity($viewerId)) {
			return [];
		}

		$ids = [$viewerId];
		foreach ($comments as $comment) {
			$ids[] = $comment->getUserId();
		}

		$settings = $this->loadSettings($ids);

		return array_filter(
			$comments,
			fn (CommentEntity $comment): bool => $this->passesSymmetricBlock(
				$comment->getUserId(),
				$viewerId,
				$settings,
			),
		);
	}

	public function getMutualBlockIds(int $viewerId, array $participantIds): array
	{
		if ($viewerId === 0 || $participantIds === []) {
			return [];
		}

		$ids = array_values(array_unique(array_merge([$viewerId], $participantIds)));
		$settings = $this->loadSettings($ids);

		$viewerBlockList = ($settings[$viewerId] ?? UserSettingsEntity::from([]))->getBlockList();

		// Start with every ID the viewer has explicitly blocked.
		$excludeIds = $viewerBlockList;

		// Add any participant who has the viewer in their own block list.
		foreach ($participantIds as $participantId) {
			$participantBlockList = ($settings[$participantId] ?? UserSettingsEntity::from([]))->getBlockList();
			if (in_array($viewerId, $participantBlockList, true)) {
				$excludeIds[] = $participantId;
			}
		}

		return array_values(array_unique($excludeIds));
	}

	/**
	 * @param int[] $ids
	 * @return array<int, UserSettingsEntity>
	 */
	private function loadSettings(array $ids): array
	{
		return $this->userSettingsRepository->getByIds(
			array_values(array_unique(array_filter($ids, static fn (int $id): bool => $id > 0))),
		);
	}

	/**
	 * @param array<int, UserSettingsEntity> $settings
	 */
	private function passesAllGates(int $authorId, int $wallOwnerId, int $viewerId, array $settings): bool
	{
		// Self-authored posts skip the inclusion and feature gates: a user
		// is implicitly part of their own social graph and their own
		// generalWall opt-out does not apply to themselves. Safety gates
		// still run so that a wall owner who blocked the viewer hides the
		// post even when the viewer authored it.
		if ($authorId !== $viewerId) {
			$viewerSettings = $settings[$viewerId] ?? UserSettingsEntity::from([]);
			$authorSettings = $settings[$authorId] ?? UserSettingsEntity::from([]);

			$buddies = $viewerSettings->getBuddies();
			if (!in_array($authorId, $buddies, true) && !in_array($wallOwnerId, $buddies, true)) {
				return false;
			}

			if ($authorSettings->getGeneralWall() === 0) {
				return false;
			}
		}

		return $this->passesSafetyGates($authorId, $wallOwnerId, $viewerId, $settings);
	}

	/**
	 * @param array<int, UserSettingsEntity> $settings
	 */
	private function passesSafetyGates(int $authorId, int $wallOwnerId, int $viewerId, array $settings): bool
	{
		$viewerSettings = $settings[$viewerId] ?? UserSettingsEntity::from([]);
		$authorSettings = $settings[$authorId] ?? UserSettingsEntity::from([]);
		$wallOwnerSettings = $settings[$wallOwnerId] ?? UserSettingsEntity::from([]);

		if (in_array($viewerId, $authorSettings->getBlockList(), true)) {
			return false;
		}

		if (in_array($authorId, $viewerSettings->getBlockList(), true)) {
			return false;
		}

		if (in_array($viewerId, $wallOwnerSettings->getBlockList(), true)) {
			return false;
		}

		if (in_array($wallOwnerId, $viewerSettings->getBlockList(), true)) {
			return false;
		}

		return true;
	}

	/**
	 * @param array<int, UserSettingsEntity> $settings
	 */
	private function passesSymmetricBlock(int $authorId, int $viewerId, array $settings): bool
	{
		$viewerSettings = $settings[$viewerId] ?? UserSettingsEntity::from([]);
		$authorSettings = $settings[$authorId] ?? UserSettingsEntity::from([]);

		if (in_array($viewerId, $authorSettings->getBlockList(), true)) {
			return false;
		}

		if (in_array($authorId, $viewerSettings->getBlockList(), true)) {
			return false;
		}

		return true;
	}
}
