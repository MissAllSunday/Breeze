<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\StatusEntity;
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
		protected StatusRepositoryInterface   $statusRepository,
		protected SettingsRepositoryInterface $userRepository,
		protected PermissionsServiceInterface $permissionsService,
		protected WallVisibilityServiceInterface $wallVisibilityService,
		protected ?EventServiceProvider $eventServiceProvider = null
	) {
		parent::__construct($statusRepository);
	}

	public function getRepository(): StatusRepositoryInterface
	{
		return $this->statusRepository;
	}

	public function getByProfile(int $wallId, ?string $cursor = null): array
	{
		$wallUserSettings = $this->userRepository->getById($wallId);
		$wallUserPagination = $wallUserSettings->getPaginationNumber();
		$currentUserInfo = $this->currentUserInfo();
		$viewerId = (int) ($currentUserInfo['id'] ?? 0);

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

		return [
			'data' => array_values($visibleStatuses),
			'permissions' => $this->permissionsService->permissions($wallId, $viewerId),
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

		return [
			'data' => array_values($visibleStatuses),
			'permissions' => $this->permissionsService->permissions(0, $viewerId),
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

		$visibleStatuses = $this->wallVisibilityService->filterStatusesForWall(
			[$statusEntity->getId() => $statusEntity],
			$viewerId,
		);

		if ($visibleStatuses === []) {
			throw new DataNotFoundException('error_no_status');
		}

		$this->filterCommentsOnStatuses($visibleStatuses, $viewerId);

		return [
			'data' => array_values($visibleStatuses),
			'permissions' => $this->permissionsService->permissions($wallId, $viewerId),
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
		$statusEntities = $this->statusRepository->insert(StatusEntity::from($data));

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
