<?php

declare(strict_types=1);


namespace Breeze\Controller\User;

use Breeze\Controller\BaseController;
use Breeze\Controller\ControllerInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Service\WallServiceInterface;

class WallController extends BaseController implements ControllerInterface
{
	public const SUB_ACTIONS = [
		'main',
		'status',
		'comment',
	];

	/**
	 * @var WallServiceInterface
	 */
	private $wallService;

	/**
	 * @var UserServiceInterface
	 */
	private $userService;

	public function __construct(WallServiceInterface $wallService, UserServiceInterface $userService)
	{
		$this->wallService = $wallService;
		$this->userService = $userService;
	}

	public function dispatch(): void
	{
		$this->wallService->initPage();
		$this->subActionCall();
	}

	public function main(): void
	{
		$this->wallService->isAllowedToSeePage(true);

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
}
