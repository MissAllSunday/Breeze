<?php

declare(strict_types=1);

namespace Breeze\Service\Actions;

use Breeze\Breeze;
use Breeze\Model\UserModel;
use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Traits\PersistenceTrait;
use Breeze\Util\Components;
use Breeze\Util\Form\UserSettingsBuilder;
use Breeze\Util\Json;

class UserSettingsService extends ActionsBaseService implements UserSettingsServiceInterface
{
	use PersistenceTrait;

	private UserRepositoryInterface $userRepository;

	private Components $components;

	private UserSettingsBuilder $SettingsBuilder;

	public function __construct(
		UserRepositoryInterface $userRepository,
		Components $components,
		UserSettingsBuilder $SettingsBuilder
	) {
		$this->userRepository = $userRepository;
		$this->components = $components;
		$this->SettingsBuilder = $SettingsBuilder;
	}

	public function init(array $subActions):void
	{
		$this->setLanguage(Breeze::NAME);
		$this->setTemplate(Breeze::NAME . self::TEMPLATE);
	}

	public function save(array $userSettings, int $userId): bool
	{
		$toInsert = [];

		foreach ($userSettings as $name => $value) {
			if (in_array($name, UserModel::JSON_VALUES)) {
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
