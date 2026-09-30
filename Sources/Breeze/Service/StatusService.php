<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\StatusEntity;
use Breeze\Enums\PermissionsEnum;
use Breeze\Event\EventServiceProvider;
use Breeze\Event\Status\StatusCreatedEvent;
use Breeze\Repository\InvalidStatusException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Repository\User\SettingsRepositoryInterface;
use Breeze\Traits\CacheTrait;
use Breeze\Traits\SettingsTrait;
use Breeze\Util\Validate\DataNotFoundException;

class StatusService extends BaseService implements StatusServiceInterface
{
	use SettingsTrait;
	use CacheTrait;

	public function __construct(
		protected StatusRepositoryInterface      $statusRepository,
		protected SettingsRepositoryInterface    $userRepository,
		protected PermissionsServiceInterface    $permissionsService,
		protected WallVisibilityServiceInterface $wallVisibilityService,
		protected ?EventServiceProvider          $eventServiceProvider = null,
		protected ?MentionServiceInterface       $mentionService = null
	) {
		parent::__construct($statusRepository);
	}

	public function getRepository(): StatusRepositoryInterface
	{
		return $this->statusRepository;
	}

	/**
	 * @throws DataNotFoundException when the target wall is disabled or
	 *                               either side blocked the other
	 */
	public function getByProfile(int $wallId, ?string $cursor = null): array
	{
		$wallUserSettings = $this->userRepository->getById($wallId);
		$wallUserPagination = $wallUserSettings->getPaginationNumber();
		$currentUserInfo = $this->currentUserInfo();
		$viewerId = (int) ($currentUserInfo['id'] ?? 0);

		// Same 404 shape used for filtered-out statuses so a disabled or
		// blocked wall does not leak that it exists.
		if (!$this->wallVisibilityService->canAccessWall($wallId, $viewerId)) {
			throw new DataNotFoundException('error_no_status');
		}

		$statusByProfile = $this->statusRepository->getByProfile(
			[$wallId],
			$wallUserPagination,
			$cursor
		);

		// Generate next cursor from the repo result before filtering so
		// pagination stays consistent with the repo's view.
		$nextCursor = null;
		$hasMore = false;
		if ($statusByProfile !== []) {
			$nextCursor = $this->statusRepository->getNextCursor($statusByProfile);
			$hasMore = count($statusByProfile) === $wallUserPagination;
		}

		$visibleStatuses = $this->wallVisibilityService->filterStatusesForWall($statusByProfile, $viewerId);
		$this->filterCommentsOnStatuses($visibleStatuses, $viewerId);
		$this->setCanDeleteFlags($visibleStatuses);

		return [
			'data' => array_values($visibleStatuses),
			'permissions' => $this->permissionsService->permissions($wallId),
			'pagination' => [
				'nextCursor' => $hasMore ? $nextCursor : null,
				'hasMore' => $hasMore,
			],
			'total' => $this->getCachedCount(StatusEntity::WALL_ID, [$wallId]),
		];
	}

	public function getCount(string $columnName, array $ids = []): int
	{
		return $this->statusRepository->getCount([
			'columnName' => $columnName,
			'ids' => $ids,
		]);
	}

	/**
	 * Get cached count with 5-minute TTL
	 */
	protected function getCachedCount(string $columnName, array $ids): int
	{
		$cacheKey = sprintf('count_%s_%s', $columnName, implode('_', $ids));

		$cached = $this->getCache($cacheKey, 300);
		if ($cached !== null && $cached !== []) {
			return is_int($cached) ? $cached : 0;
		}

		$count = $this->getCount($columnName, $ids);

		// Cache for 5 minutes (300 seconds)
		$this->setCache($cacheKey, $count, 300);

		return $count;
	}

	public function getByBuddies(?string $cursor = null): array
	{
		$currentUserInfo = $this->currentUserInfo();
		$viewerId = (int) ($currentUserInfo['id'] ?? 0);
		$currentUserSettings = $this->userRepository->getById($viewerId);
		$currentUserBuddies = $currentUserSettings->getBuddies();
		$currentUserPagination = $currentUserSettings->getPaginationNumber();

		// Always include the viewer's own ID so their own posts appear on the
		// general wall even when they have no buddies yet.
		$feedIds = array_values(array_unique(array_merge([$viewerId], $currentUserBuddies)));

		// Pre-compute the mutual block set so the repo can exclude those rows
		// at the SQL level, reducing rows fetched and improving pagination density.
		$excludeIds = $this->wallVisibilityService->getMutualBlockIds($viewerId, $currentUserBuddies);

		$statusByBuddies = $this->statusRepository->getByBuddyActivity(
			$feedIds,
			$currentUserPagination,
			$cursor,
			$excludeIds,
			$viewerId
		);

		// Generate next cursor from the repo result before filtering so
		// pagination stays consistent with the repo's view.
		$nextCursor = null;
		$hasMore = false;
		if ($statusByBuddies !== []) {
			$nextCursor = $this->statusRepository->getNextCursor($statusByBuddies);
			$hasMore = count($statusByBuddies) === $currentUserPagination;
		}

		$visibleStatuses = $this->wallVisibilityService->filterStatusesForFeed($statusByBuddies, $viewerId);
		$this->filterCommentsOnStatuses($visibleStatuses, $viewerId);
		$this->setCanDeleteFlags($visibleStatuses);

		return [
			'data' => array_values($visibleStatuses),
			// On the general wall there is no single profile owner: pass 0 so
			// PermissionsService evaluates actual permissions instead of granting
			// post rights unconditionally via the profile-owner shortcut.
			'permissions' => $this->permissionsService->permissions(0),
			'pagination' => [
				'nextCursor' => $hasMore ? $nextCursor : null,
				'hasMore' => $hasMore,
			],
			'total' => $this->getCachedCount(StatusEntity::USER_ID, $feedIds),
		];
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function getById(int $statusId): array
	{
		$currentUserInfo = $this->currentUserInfo();
		$viewerId = (int) ($currentUserInfo['id'] ?? 0);
		$statusEntity = $this->statusRepository->getById($statusId);
		$wallId = $statusEntity->getWallId();

		if (!$this->wallVisibilityService->canAccessWall($wallId, $viewerId)) {
			throw new DataNotFoundException('error_no_status');
		}

		$visibleStatuses = $this->wallVisibilityService->filterStatusesForWall(
			[$statusEntity->getId() => $statusEntity],
			$viewerId,
		);

		if ($visibleStatuses === []) {
			throw new DataNotFoundException('error_no_status');
		}

		$this->filterCommentsOnStatuses($visibleStatuses, $viewerId);
		$this->setCanDeleteFlags($visibleStatuses);

		return [
			'data' => array_values($visibleStatuses),
			'permissions' => $this->permissionsService->permissions($wallId),
			'pagination' => [
				'nextCursor' => null,
				'hasMore' => false,
			],
			'total' => count($visibleStatuses),
		];
	}

	/**
	 * @param array<int, StatusEntity> $statuses
	 */
	private function filterCommentsOnStatuses(array $statuses, int $viewerId): void
	{
		foreach ($statuses as $status) {
			$status->setComments(
				$this->wallVisibilityService->filterVisibleComments($status->getComments(), $viewerId)
			);
		}
	}

	/**
	 * Resolve the per-item `canDelete` flag for every status and its visible
	 * comments.
	 *
	 * The wall-level `permissions.*.delete` snapshot only answers "may delete
	 * ANY item on this wall"; own-content rights live in canDelete(), so the
	 * UI needs a per-item flag or members lose the delete button on their own
	 * posts. Both ids come from the persisted entities, never from a payload.
	 *
	 * @param array<int, StatusEntity> $statuses
	 */
	private function setCanDeleteFlags(array $statuses): void
	{
		foreach ($statuses as $status) {
			$wallId = $status->getWallId();

			$status->setCanDelete($this->permissionsService->canDelete(
				PermissionsEnum::TYPE_STATUS,
				$status->getUserId(),
				$wallId
			));

			foreach ($status->getComments() as $comment) {
				$comment->setCanDelete($this->permissionsService->canDelete(
					PermissionsEnum::TYPE_COMMENTS,
					$comment->getUserId(),
					$wallId
				));
			}
		}
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function deleteById(int $statusId): void
	{
		$this->statusRepository->deleteById($statusId);
	}

	/**
	 * @throws InvalidStatusException
	 * @return array [StatusEntity]
	 */
	public function save(array $data): array
	{
		$processed = null;

		if ($this->mentionService?->isEnabled()) {
			$mentionIds = array_map('intval', (array) ($data['mention_ids'] ?? []));
			$processed  = $this->mentionService->processBody($data[StatusEntity::BODY], $mentionIds);
			$data[StatusEntity::BODY] = $processed['body'];
		}

		$statusEntities = $this->statusRepository->insert(StatusEntity::from($data));

		// Per-item delete flag, resolved from the persisted author id and
		// wall_id so the freshly posted status renders its delete button
		// without a page reload.
		foreach ($statusEntities as $entity) {
			$entity->setCanDelete($this->permissionsService->canDelete(
				PermissionsEnum::TYPE_STATUS,
				$entity->getUserId(),
				$entity->getWallId()
			));
		}

		if ($processed !== null && !empty($processed['members'])) {
			foreach ($statusEntities as $entity) {
				$this->mentionService->save(
					MentionServiceInterface::CONTENT_TYPE_STATUS,
					$entity->getId(),
					$processed['members'],
					$entity->getUserId(),
					$entity->getWallId()
				);
			}
		}

		// Dispatch the status created event
		if (isset($this->eventServiceProvider)) {
			$this->eventServiceProvider->getDispatcher()->dispatch(new StatusCreatedEvent($statusEntities));
		}

		return $statusEntities;
	}

	public function currentUserInfo(): array
	{
		return  $this->global('user_info');
	}

	public function recountComments(): void
	{
		$this->statusRepository->recountComments();
	}

	public function recountLikes(): void
	{
		$this->statusRepository->recountLikes();
	}
}
