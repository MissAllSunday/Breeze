<?php

declare(strict_types=1);

namespace Breeze\Repository\User;

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
		$userSettings = $this->getCache('user_settings_' . $userId);

		if (null === $userSettings)
		{
			$userSettings = $this->userModel->getUserSettings($userId);
			$this->setCache('user_settings_' . $userId, $userSettings);
		}

		return $userSettings;
	}

	public function getModel(): UserModelInterface
	{
		return $this->userModel;
	}
}
