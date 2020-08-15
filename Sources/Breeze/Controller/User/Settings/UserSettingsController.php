<?php

declare(strict_types=1);

namespace Breeze\Controller\User\Settings;

use Breeze\Controller\BaseController;
use Breeze\Controller\ControllerInterface;
use Breeze\Service\Actions\UserSettingsServiceInterface;
use Breeze\Service\UserServiceInterface;

class UserSettingsController extends BaseController implements ControllerInterface
{
	public const ACTION_MAIN = 'main';
	public const ACTION_SAVE = 'save';
	public const SUB_ACTIONS = [
		self::ACTION_MAIN,
		self::ACTION_SAVE,
	];

	/**
	 */
	private UserServiceInterface $userService;

	/**
	 */
	private UserSettingsServiceInterface $userSettingsService;

	public function __construct(UserSettingsServiceInterface $userSettingsService, UserServiceInterface $userService)
	{
		$this->userService = $userService;
		$this->userSettingsService = $userSettingsService;
	}

	public function dispatch(): void
	{
		$this->userSettingsService->init($this->getSubActions());
		$this->subActionCall();
	}

	public function main(): void
	{
		$this->render(__FUNCTION__, []);
	}

	public function render(string $subTemplate, array $params = [], string $smfTemplate = ''): void
	{
		$this->userSettingsService->defaultSubActionContent($subTemplate, $params, $smfTemplate);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function getMainAction(): string
	{
		return self::ACTION_MAIN;
	}
}
