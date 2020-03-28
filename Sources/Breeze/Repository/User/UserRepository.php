<?php

declare(strict_types=1);

namespace Breeze\Repository\User;

use Breeze\Repository\BaseRepository;
use Breeze\Repository\RepositoryInterface;

class UserRepository extends BaseRepository implements RepositoryInterface
{
	public function getUserSettings(int $userId): array
	{
		$userSettings = $this->getCache('user_settings_' . $userId);

		if (null === $userSettings)
		{
			$userSettings = $this->model->getUserSettings($userId);
			$this->setCache('user_settings_' . $userId, $userSettings);
		}

		return $userSettings;
	}
}
