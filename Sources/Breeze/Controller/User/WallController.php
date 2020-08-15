<?php

declare(strict_types=1);


namespace Breeze\Controller\User;

use Breeze\Controller\BaseController;
use Breeze\Controller\ControllerInterface;
use Breeze\Service\Actions\WallServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Components;

class WallController extends BaseController implements ControllerInterface
{
	public const ACTION_MAIN = 'main';
	public const ACTION_STATUS = 'status';
	public const ACTION_COMMENT = 'comment';
	public const SUB_ACTIONS = [
		self::ACTION_MAIN,
		self::ACTION_COMMENT,
		self::ACTION_STATUS,
	];

	private WallServiceInterface $wallService;

	private UserServiceInterface $userService;

	private Components $components;

	public function __construct(
		WallServiceInterface $wallService,
		UserServiceInterface $userService
	)
	{
		$this->wallService = $wallService;
		$this->userService = $userService;
	}

	public function dispatch(): void
	{
		$this->wallService->init($this->getSubActions());
		$this->subActionCall();
	}

	public function main(): void
	{
		$this->render(__FUNCTION__);
	}

	public function render(string $subTemplate, array $params = [], string $smfTemplate = ''): void
	{
		$this->wallService->loadCSS();
		$this->wallService->defaultSubActionContent($subTemplate, $params, $smfTemplate);
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
