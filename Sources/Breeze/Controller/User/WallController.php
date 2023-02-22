<?php

declare(strict_types=1);


namespace Breeze\Controller\User;

use Breeze\Controller\BaseController;
use Breeze\Controller\ControllerInterface;
use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Service\ProfileServiceInterface;
use Breeze\Util\Response;

class WallController extends BaseController implements ControllerInterface
{
	public const ACTION_GENERAL = 'general';
	public const SUB_ACTIONS = [
		self::ACTION_GENERAL,
	];

	public function __construct(
		protected UserRepositoryInterface $userRepository,
		protected Response $response,
		protected ProfileServiceInterface $profileService
	) {
	}

	public function general(): void
	{
		$currentUserInfo = $this->global('user_info');

		$this->render(__FUNCTION__);

		$this->profileService->loadComponents($currentUserInfo['id']);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function getMainAction(): string
	{
		return self::ACTION_GENERAL;
	}

	public function getActionName(): string
	{
		return self::ACTION_GENERAL;
	}
}
