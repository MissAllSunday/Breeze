<?php

declare(strict_types=1);

namespace Breeze\Controller\User;

use Breeze\Controller\BaseController;
use Breeze\Service\ProfileServiceInterface;
use Breeze\Util\Error;
use Breeze\Util\Response;

class WallController extends BaseController
{
	public const string ACTION_WALL = 'wall';
	public const string ACTION_PROFILE = 'profile';
	public const array SUB_ACTIONS = [
		self::ACTION_WALL,
		self::ACTION_PROFILE,
	];

	public function __construct(
		protected Response $response,
		protected ProfileServiceInterface $profileService
	) {
	}

	public function wall(): void
	{
		$currentUserInfo = $this->global('user_info');
		$this->profileService->setEditor();

		if (!empty($this->getRequest('id', 0))) {
			$this->setContextVars([
				'page_title' => $this->getText('user_single_status'),
			]);
		}

		$this->render(__FUNCTION__);
		$this->profileService->loadComponents($currentUserInfo['id']);
	}

	public function profile(): void
	{
		$profileId = $this->getRequest('u', 0);
		$buddiesData = [];

		if (empty($profileId)) {
			Error::show('no_valid_action');
		}

		$profileSettings = $this->profileService->getUserSettings($profileId);
		$currentUserInfo = $this->profileService->getCurrentUserInfo();
		$profileBuddies = $profileSettings->getBuddies();

		if (!$this->profileService->isAllowedToSeePage($profileSettings, $profileId, $currentUserInfo['id'])) {
			Error::show('error_no_access');
		}

		if ($profileSettings->getEnableBuddiesTab() !== 0 && $profileBuddies !== []) {
			$buddiesData = $this->profileService->loadUsersInfo($profileBuddies);
		}

		$this->profileService->setEditor();

		$this->render(__FUNCTION__, [
			'profileSettings' => $profileSettings,
			'buddiesData' => $buddiesData,
		]);

		$this->profileService->loadComponents($profileId);
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
		return self::ACTION_WALL;
	}

	public function getActionName(): string
	{
		return self::ACTION_WALL;
	}
}
