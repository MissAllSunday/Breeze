<?php

declare(strict_types=1);

namespace Breeze\Service\Actions;

use Breeze\Breeze;
use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Util\Components;
use Breeze\Util\FormBuilder;


class UserSettingsService extends ActionsBaseService implements UserSettingsServiceInterface
{
	private UserRepositoryInterface $userRepository;

	private Components $components;

	private FormBuilder $formBuilder;

	public function __construct(
		UserRepositoryInterface $userRepository,
		Components $components,
		FormBuilder $formBuilder
	)
	{
		$this->userRepository = $userRepository;
		$this->components = $components;
		$this->formBuilder = $formBuilder;
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
