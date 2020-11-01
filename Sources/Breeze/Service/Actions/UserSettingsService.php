<?php

declare(strict_types=1);

namespace Breeze\Service\Actions;

use Breeze\Breeze;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Util\Components;
use Breeze\Util\Form\UserSettingsBuilder;


class UserSettingsService extends ActionsBaseService implements UserSettingsServiceInterface
{
	private UserRepositoryInterface $userRepository;

	private Components $components;

	private UserSettingsBuilder $SettingsBuilder;

	public function __construct(
		UserRepositoryInterface $userRepository,
		Components $components,
		UserSettingsBuilder $SettingsBuilder
	)
	{
		$this->userRepository = $userRepository;
		$this->components = $components;
		$this->SettingsBuilder = $SettingsBuilder;
	}

	public function init(array $subActions):void
	{
		$this->setLanguage(Breeze::NAME);
		$this->setTemplate(Breeze::NAME . self::TEMPLATE);
	}

	public function save(array $userSettings, int $userId): void
	{
		$userSettings = array_replace(UserSettingsEntity::getDefaultValues(), $userSettings);

		var_dump($userSettings);die;
	}

	public function getActionName(): string
	{
		return self::AREA;
	}
}
