<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\UserSettingsEntity;
use Breeze\PermissionsEnum;
use Breeze\Repository\InvalidStatusException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Traits\SettingsTrait;

class StatusService
{
	use SettingsTrait;

	public function __construct(
		protected StatusRepositoryInterface $statusRepository,
		protected UserRepositoryInterface $userRepository,
		protected PermissionsService $permissionsService
	)
	{

	}

	public function getWallUserSettings(int $wallId, string $valueName = ''): mixed
	{
		$wallUserSettings = $this->userRepository->getById($wallId);

		return !empty($valueName) ? $wallUserSettings[$valueName] : $wallUserSettings;
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function getByProfile(int $wallId, int $start): array
	{
		$wallUserPagination = $this->getWallUserSettings($wallId, UserSettingsEntity::PAGINATION_NUM);
		$currentUserInfo = $this->currentUserInfo();

		$statusByProfile = $this->statusRepository->getByProfile(
			[$wallId],
			$start,
			!empty($start) ? ($wallUserPagination * $start) : $wallUserPagination
		);
		$statusByProfile[PermissionsEnum::NAME] = $this->permissionsService->permissions('any', $wallId, $currentUserInfo['id']);

		return $statusByProfile;
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function getByBuddies(int $start): array
	{
		$currentUserInfo = $this->currentUserInfo();
		$currentUserSettings = $this->userRepository->getById($currentUserInfo['id']);
		$currentUserBuddies = $currentUserSettings[UserSettingsEntity::BUDDIES];

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
	public function save(array $data): array
	{
		$statusId = $this->statusRepository->save($data);
		$status = $this->statusRepository->getById($statusId);
		$status[$statusId]['isNew'] = true;

		return $status;
	}

	protected function currentUserInfo(): array
	{
		return  $this->global('user_info');
	}
}
