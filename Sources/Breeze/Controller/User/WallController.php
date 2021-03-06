<?php

declare(strict_types=1);


namespace Breeze\Controller\User;

use Breeze\Controller\BaseController;
use Breeze\Controller\ControllerInterface;
use Breeze\Entity\SettingsEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Service\Actions\WallServiceInterface;
use Breeze\Service\UserService;
use Breeze\Service\UserServiceInterface;

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

	private int $userId;

	public function __construct(
		WallServiceInterface $wallService,
		UserServiceInterface $userService
	) {
		$this->wallService = $wallService;
		$this->userService = $userService;
	}

	public function dispatch(): void
	{
		$this->userId = $this->getRequest('u');
		$this->wallService->init($this->getSubActions());
		$this->subActionCall();
	}

	public function main(): void
	{
		$scriptUrl = $this->global('scripturl');
		$userSettings = $this->userService->getUserSettings($this->userId);
		$forceWall = $this->getSetting(SettingsEntity::FORCE_WALL);

		if (empty($userSettings[UserSettingsEntity::WALL]) && empty($forceWall)) {
			$this->userService->redirect($scriptUrl . sprintf(UserService::LEGACY_URL, $this->userId));
		}

		$this->render(__FUNCTION__, [
			'userSettings' => $userSettings,
		]);
	}

	public function render(string $subTemplate, array $params = [], string $smfTemplate = ''): void
	{
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
