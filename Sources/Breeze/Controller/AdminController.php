<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Breeze;
use Breeze\Entity\SettingsEntity;
use Breeze\Repository\User\MoodRepositoryInterface;
use Breeze\Service\Actions\AdminServiceInterface;
use Breeze\Traits\PersistenceTrait;

class AdminController extends BaseController implements ControllerInterface
{
	use PersistenceTrait;

	public const ACTION_MAIN = 'main';
	public const ACTION_SETTINGS = 'settings';
	public const ACTION_PERMISSIONS = 'permissions';
	public const ACTION_MOOD_LIST = 'moodList';
	public const ACTION_DONATE = 'donate';

	public const SUB_ACTIONS = [
		self::ACTION_MAIN,
		self::ACTION_SETTINGS,
		self::ACTION_PERMISSIONS,
		self::ACTION_MOOD_LIST,
		self::ACTION_DONATE,
	];

	public function __construct(
		protected AdminServiceInterface $adminService,
		protected MoodRepositoryInterface  $moodRepository
	) {
	}

	public function dispatch(): void
	{
		$this->adminService->init($this->getSubActions());

		$this->subActionCall();
	}

	public function main(): void
	{
		$this->render(__FUNCTION__, [
			Breeze::NAME => [
				'credits' => Breeze::credits(),
				'version' => Breeze::VERSION,
				'react' => Breeze::REACT_VERSION,
			],
		]);

		$this->adminService->loadComponents(['adminMain', 'feed']);
	}

	public function settings(): void
	{
		$this->render(__FUNCTION__, [], 'show_settings');

		$saving = $this->isRequestSet('save');

		$this->adminService->configVars($saving);

		if ($saving) {
			$this->adminService->redirect(AdminServiceInterface::POST_URL . __FUNCTION__);
		}
	}

	public function permissions(): void
	{
		$this->render(__FUNCTION__, [], 'show_settings');

		$saving = $this->isRequestSet('save');

		$this->adminService->permissionsConfigVars($saving);

		if ($saving) {
			$this->adminService->redirect(AdminServiceInterface::POST_URL . __FUNCTION__);
		}
	}

	public function donate(): void
	{
		$this->render(__FUNCTION__);
	}

	public function moodList(): void
	{
		$this->adminService->isEnableFeature(
			SettingsEntity::ENABLE_MOOD,
			AdminServiceInterface::POST_URL . 'main'
		);

		$this->moodRepository->getAllMoods();

		$this->render(__FUNCTION__);
	}

	public function render(string $subActionName, array $templateParams = [], string $smfTemplate = ''): void
	{
		$this->adminService->defaultSubActionContent(
			$subActionName,
			$templateParams,
			$smfTemplate
		);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function getMainAction(): string
	{
		return self::ACTION_MAIN;
	}

	public function getActionName(): string
	{
		return self::ACTION_MAIN;
	}
}
