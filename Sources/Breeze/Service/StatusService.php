<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\StatusEntity;
use Breeze\Event\EventServiceProvider;
use Breeze\Event\Status\StatusCreatedEvent;
use Breeze\Repository\InvalidStatusException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Repository\User\SettingsRepositoryInterface;
use Breeze\Traits\SettingsTrait;
use Breeze\Util\Validate\EmptyDataException;

class StatusService extends BaseService implements StatusServiceInterface
{
	use SettingsTrait;

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
	public function getByProfile(int $wallId, int $start): array
	{
		$wallUserSettings = $this->userRepository->getById($wallId);
		$wallUserPagination = $wallUserSettings->getPaginationNumber();
		$currentUserInfo = $this->currentUserInfo();

		$statusByProfile = $this->statusRepository->getByProfile(
			[$wallId],
			$start,
			$wallUserPagination
		);

		return [
			'data' => $statusByProfile,
			'permissions' => $this->permissionsService->permissions($wallId, $currentUserInfo['id']),
			'total' => $this->getCount(StatusEntity::WALL_ID, [$wallId]),
		];
	}

	public function getCount(string $columnName, array $ids = []): int
	{
		return $this->statusRepository->getCount([
			'columnName' => $columnName,
			'ids' => $ids,
		]);
	}

	public function getByBuddies(int $start): array
	{
		$currentUserInfo = $this->currentUserInfo();
		$currentUserSettings = $this->userRepository->getById($currentUserInfo['id']);
		$currentUserBuddies = $currentUserSettings->getBuddies();
		$currentUserPagination = $currentUserSettings->getPaginationNumber();

		if (empty($currentUserBuddies)) {
			return [];
		}

		$statusByBuddies = $this->statusRepository->getBy(
			StatusEntity::USER_ID,
			$currentUserBuddies,
			$start,
			$currentUserPagination
		);

		return [
			'data' => $statusByBuddies,
			'permissions' => $this->permissionsService->permissions(0, $currentUserInfo['id']),
			'total' => $this->getCount(StatusEntity::USER_ID, $currentUserBuddies),
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
}
