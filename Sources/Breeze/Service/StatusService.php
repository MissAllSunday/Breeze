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
use Breeze\Util\Validate\EmptyDataException;

class StatusService extends BaseService implements StatusServiceInterface
{
	use SettingsTrait;
	use CacheTrait;

	public function __construct(
		protected StatusRepositoryInterface   $statusRepository,
		protected SettingsRepositoryInterface $userRepository,
		protected PermissionsServiceInterface $permissionsService,
		protected ?EventServiceProvider $eventServiceProvider = null
	) {
		parent::__construct($statusRepository);
	}

	public function getRepository(): StatusRepositoryInterface
	{
		return $this->statusRepository;
	}

	/**
	 * @throws EmptyDataException
	 */
	public function getByProfile(int $wallId, ?string $cursor = null): array
	{
		$wallUserSettings = $this->userRepository->getById($wallId);
		$wallUserPagination = $wallUserSettings->getPaginationNumber();
		$currentUserInfo = $this->currentUserInfo();

		$statusByProfile = $this->statusRepository->getByProfile(
			[$wallId],
			$wallUserPagination,
			$cursor
		);

		// Generate next cursor
		$nextCursor = null;
		$hasMore = false;
		if ($statusByProfile !== []) {
			$nextCursor = $this->statusRepository->getNextCursor($statusByProfile);
			$hasMore = count($statusByProfile) === $wallUserPagination;
		}

		return [
			'data' => $statusByProfile,
			'permissions' => $this->permissionsService->permissions($wallId, $currentUserInfo['id']),
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
		$currentUserSettings = $this->userRepository->getById($currentUserInfo['id']);
		$currentUserBuddies = $currentUserSettings->getBuddies();
		$currentUserPagination = $currentUserSettings->getPaginationNumber();

		if ($currentUserBuddies === []) {
			return [];
		}

		$statusByBuddies = $this->statusRepository->getBy(
			StatusEntity::USER_ID,
			$currentUserBuddies,
			$currentUserPagination,
			$cursor
		);

		// Generate next cursor
		$nextCursor = null;
		$hasMore = false;
		if ($statusByBuddies !== []) {
			$nextCursor = $this->statusRepository->getNextCursor($statusByBuddies);
			$hasMore = count($statusByBuddies) === $currentUserPagination;
		}

		return [
			'data' => $statusByBuddies,
			'permissions' => $this->permissionsService->permissions(0, $currentUserInfo['id']),
			'pagination' => [
				'nextCursor' => $hasMore ? $nextCursor : null,
				'hasMore' => $hasMore,
			],
			'total' => $this->getCachedCount(StatusEntity::USER_ID, $currentUserBuddies),
		];
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function getById(int $statusId): array
	{
		$currentUserInfo = $this->currentUserInfo();
		$statusEntity = $this->statusRepository->getById($statusId);
		$wallId = $statusEntity->getWallId();

		return [
			'data' => [$statusEntity],
			'permissions' => $this->permissionsService->permissions($wallId, $currentUserInfo['id']),
			'pagination' => [
				'nextCursor' => null,
				'hasMore' => false,
			],
			'total' => 1,
		];
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
