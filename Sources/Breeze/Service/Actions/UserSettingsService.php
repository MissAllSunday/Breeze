<?php

declare(strict_types=1);

namespace Breeze\Service\Actions;

use Breeze\Breeze;
use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Util\Components;
use Breeze\Util\Form\SettingsBuilder;


class UserSettingsService extends ActionsBaseService implements UserSettingsServiceInterface
{
	private UserRepositoryInterface $userRepository;

	private Components $components;

	private SettingsBuilder $SettingsBuilder;

	public function __construct(
		UserRepositoryInterface $userRepository,
		Components $components,
		SettingsBuilder $SettingsBuilder
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

	public function getActionName(): string
	{
		return self::AREA;
	}
}
