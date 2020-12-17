<?php

declare(strict_types=1);

namespace Breeze\Repository\User;

use Breeze\Entity\OptionsEntity;
use Breeze\Model\UserModelInterface;
use Breeze\Repository\BaseRepository;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
	private UserModelInterface $userModel;

	public function __construct(UserModelInterface $userModel)
	{
		$this->userModel = $userModel;
	}

	public function getUserSettings(int $userId): array
	{
		$userSettings = $this->getCache(sprintf(OptionsEntity::CACHE_NAME, $userId));

		if (empty($userSettings)) {
			$userSettings = $this->userModel->getUserSettings($userId);
			$this->setCache(sprintf(OptionsEntity::CACHE_NAME, $userId), $userSettings);
		}

		return $userSettings;
	}

	public function save(array $userSettings, $userId): int
	{
		if ($this->userModel->insert($userSettings)) {
			$this->setCache(sprintf(OptionsEntity::CACHE_NAME, $userId), null);
		}

		return 1;
	}

	public function getModel(): UserModelInterface
	{
		return $this->userModel;
	}
}
