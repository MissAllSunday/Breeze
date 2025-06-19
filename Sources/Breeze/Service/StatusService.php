<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\StatusEntity;
use Breeze\Entity\StatusHandledEntity;
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
		protected PermissionsServiceInterface $permissionsService
	) {
		parent::__construct($statusRepository);
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

	/**
	 * @throws EmptyDataException
	 */
	public function getByBuddies(int $start): array
	{
		$currentUserInfo = $this->currentUserInfo();
		$currentUserSettings = $this->userRepository->getById($currentUserInfo['id']);
		$currentUserBuddies = $currentUserSettings->getBuddies();

		if (empty($currentUserBuddies)) {
			return [];
		}

		return $this->statusRepository->getByProfile(
			$currentUserBuddies,
			$start
		);
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
	 */
	public function save(array $data): StatusHandledEntity
	{
		return $this->statusRepository->insert(new StatusEntity($data));
	}

	public function currentUserInfo(): array
	{
		return  $this->global('user_info');
	}
}
