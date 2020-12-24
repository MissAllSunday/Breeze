<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Breeze;
use Breeze\Entity\SettingsEntity;
use Breeze\Service\Actions\AdminService;
use Breeze\Service\Actions\AdminServiceInterface;
use Breeze\Service\MoodServiceInterface;
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

	protected MoodServiceInterface $moodService;

	private AdminServiceInterface $adminService;

	public function __construct(
		AdminServiceInterface $adminService,
		MoodServiceInterface $moodService
	) {
		$this->adminService = $adminService;
		$this->moodService = $moodService;
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
				'vue' => Breeze::VUE_VERSION,
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
			$this->adminService->redirect(AdminService::POST_URL . __FUNCTION__);
		}
	}

	public function permissions(): void
	{
		$this->render(__FUNCTION__, [], 'show_settings');

		$saving = $this->isRequestSet('save');

		$this->adminService->permissionsConfigVars($saving);

		if ($saving) {
			$this->adminService->redirect(AdminService::POST_URL . __FUNCTION__);
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
			AdminService::POST_URL . 'main'
		);

		$this->render(__FUNCTION__, [
			Breeze::NAME => [
				'notice' => $this->getMessage(),
				'formId' => __FUNCTION__,
			],
		]);

		$start = $this->getRequest('start', 0);
		$this->moodService->createMoodList([
			'id' => __FUNCTION__,
			'base_href' => $this->adminService->global('scripturl') .
				'?' . AdminService::POST_URL . __FUNCTION__,
		], $start);

		$toDeleteMoodIds = $this->getRequest('checked_icons');

		if ($this->isRequestSet('delete') &&
			!empty($toDeleteMoodIds)) {
			$this->moodService->deleteMoods($toDeleteMoodIds);
			$this->adminService->redirect(AdminService::POST_URL . __FUNCTION__);
		}

//		$this->adminService->loadComponents(['moodList', 'textArea']);
	}

	public function render(string $subTemplate, array $params = [], string $smfTemplate = ''): void
	{
		$this->adminService->defaultSubActionContent($subTemplate, $params, $smfTemplate);
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
