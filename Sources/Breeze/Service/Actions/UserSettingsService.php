<?php

declare(strict_types=1);

namespace Breeze\Service\Actions;

use Breeze\Breeze;
use Breeze\Repository\User\UserRepositoryInterface;


class UserSettingsService extends ActionsBaseService implements UserSettingsServiceInterface
{
	/**
	 * @var UserRepositoryInterface
	 */
	private $userRepository;

	public function __construct(UserRepositoryInterface $userRepository)
	{
		$this->userRepository = $userRepository;
	}

	public function init(array $subActions):void
	{
		$this->setLanguage(Breeze::NAME);
		$this->setTemplate(Breeze::NAME);
	}

	public function getActionName(): string
	{
		return self::ACTION;
	}
}
