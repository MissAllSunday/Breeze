<?php

declare(strict_types=1);

namespace Breeze\Service\Actions;

use Breeze\Breeze;
use Breeze\Model\UserModelInterface;
use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Traits\PersistenceTrait;
use Breeze\Util\Json;

class UserSettingsService extends ActionsBaseService implements UserSettingsServiceInterface
{
	use PersistenceTrait;

	public function __construct(
		private UserRepositoryInterface $userRepository,
	) {
	}

	public function init(array $subActions): void
	{
		$this->setLanguage(Breeze::NAME);
		$this->setTemplate(Breeze::NAME . self::TEMPLATE);
	}

	public function save(array $userSettings, int $userId): bool
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

		$this->userRepository->save($toInsert, $userId);

		return true;
	}

	public function getActionName(): string
	{
		return self::AREA;
	}
}
