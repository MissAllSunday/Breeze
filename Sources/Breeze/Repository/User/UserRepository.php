<?php

declare(strict_types=1);

namespace Breeze\Repository\User;

use Breeze\Entity\OptionsEntity;
use Breeze\Model\UserModelInterface;
use Breeze\Repository\BaseRepository;
use Breeze\Util\Json;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
	private UserModelInterface $userModel;

	public function __construct(UserModelInterface $userModel)
	{
		$this->userModel = $userModel;
	}

	public function getById(int $id): array
	{
		$userSettings = $this->getCache(sprintf(OptionsEntity::CACHE_NAME, $id));

		if (empty($userSettings)) {
			$userSettings = $this->userModel->getUserSettings($id);
			$this->setCache(sprintf(OptionsEntity::CACHE_NAME, $id), $userSettings);
		}

		return $userSettings;
	}

	public function save(array $userSettings, $userId): bool
	{
		$toInsert = [];

		foreach ($userSettings as $name => $value) {
			if (in_array($name, UserModelInterface::JSON_VALUES)) {
				$value = !empty($value) ? Json::encode($value) : '';
			}

			$toInsert[] = [$userId, $name, $value];
		}

		if (empty($toInsert)) {
			return false;
		}

		if ($this->userModel->insert($userSettings)) {
			$this->setCache(sprintf(OptionsEntity::CACHE_NAME, $userId), null);
		}

		return true;
	}

	public function getModel(): UserModelInterface
	{
		return $this->userModel;
	}
}
