<?php

declare(strict_types=1);


namespace Breeze\Controller\User;

use Breeze\Controller\BaseController;
use Breeze\Controller\ControllerInterface;
use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Service\ProfileService;
use Breeze\Service\ProfileServiceInterface;
use Breeze\Util\Response;

class WallController extends BaseController implements ControllerInterface
{
	public const ACTION_PROFILE = 'profile';
	public const ACTION_GENERAL = 'general';
	public const ACTION_STATUS = 'status';
	public const ACTION_COMMENT = 'comment';
	public const SUB_ACTIONS = [
		self::ACTION_PROFILE,
		self::ACTION_GENERAL,
		self::ACTION_STATUS,
	];

	public function __construct(
		protected UserRepositoryInterface $userRepository,
		protected Response $response,
		protected ProfileServiceInterface $profileService
	) {
	}

	public function profile(): void
	{
		$profileId = $this->getRequest('u');
		$profileSettings = $this->userRepository->getById($profileId);
		$userInfo = $this->global('user_info');

		if (!$this->profileService->isAllowedToSeePage($profileSettings, (int) $profileId, (int) $userInfo['id'])) {
			$this->response->redirect($this->global('scripturl') .
			sprintf(ProfileService::LEGACY_URL, $profileId));
		}

		$this->profileService->loadComponents($profileId);

		$this->render(__FUNCTION__);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function getMainAction(): string
	{
		return self::ACTION_PROFILE;
	}

	public function getActionName(): string
	{
		return self::ACTION_PROFILE;
	}
}
