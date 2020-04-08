<?php

declare(strict_types=1);

namespace Breeze\Controller\User\Settings;

use Breeze\Controller\BaseController;
use Breeze\Controller\ControllerInterface;
use Breeze\Service\Actions\UserSettingsServiceInterface;
use Breeze\Service\UserServiceInterface;

class UserSettingsController extends BaseController implements ControllerInterface
{
	public const SUB_ACTIONS = [
		'main',
		'save',
	];

	/**
	 * @var UserServiceInterface
	 */
	private $userService;

	/**
	 * @var UserSettingsServiceInterface
	 */
	private $userSettingsService;

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

	}

	public function render(string $subTemplate, array $params): void
	{
		// TODO: Implement render() method.
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}
}
