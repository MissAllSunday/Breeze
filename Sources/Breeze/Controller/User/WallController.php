<?php

declare(strict_types=1);


namespace Breeze\Controller\User;

use Breeze\Controller\BaseController;
use Breeze\Controller\ControllerInterface;
use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Service\ProfileServiceInterface;
use Breeze\Util\Error;
use Breeze\Util\Response;

class WallController extends BaseController implements ControllerInterface
{
	public const ACTION_GENERAL = 'wall';
	public const ACTION_PROFILE = 'profile';
	public const SUB_ACTIONS = [
		self::ACTION_GENERAL,
		self::ACTION_PROFILE,
	];

	public function __construct(
		protected UserRepositoryInterface $userRepository,
		protected Response $response,
		protected ProfileServiceInterface $profileService
	) {
	}

	public function wall(): void
	{
		$currentUserInfo = $this->global('user_info');

		$this->render(__FUNCTION__);

		$this->profileService->loadComponents($currentUserInfo['id']);
	}

	public function profile(): void
	{
		$userId = $this->getRequest('u', 0);

		if (empty($userId)) {
			Error::show('no_valid_action');
		}

		$userSettings = $this->userRepository->getById($userId);

		$this->render(__FUNCTION__);

		$this->profileService->loadComponents($userId);
	}

	public function getActionVarName():string
	{
			return 'action';
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
