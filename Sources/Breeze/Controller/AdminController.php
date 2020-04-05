<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Breeze;
use Breeze\Service\AdminService;
use Breeze\Service\AdminServiceInterface;
use Breeze\Service\MoodServiceInterface;
use Breeze\Service\ServiceInterface;
use Breeze\Traits\PersistenceTrait;

class AdminController extends BaseController implements ControllerInterface
{
	use PersistenceTrait;

	public const SUB_ACTIONS = [
		'main',
		'settings',
		'permissions',
		'donate',
		'moodList',
	];

	/**
	 * @var MoodServiceInterface
	 */
	protected $moodService;

	/**
	 * @var AdminServiceInterface
	 */
	private $adminService;

	public function __construct(
		AdminServiceInterface $adminService,
		MoodServiceInterface $moodService
	)
	{
		$this->adminService = $adminService;
		$this->moodService = $moodService;
	}

	public function dispatch(): void
	{
		$this->adminService->initSettingsPage($this->getSubActions());

		$this->subActionCall();
	}

	public function main(): void
	{
		$this->render(__FUNCTION__, [
			Breeze::NAME => [
				'credits' => Breeze::credits(),
				'version' => Breeze::VERSION,
			],
		]);
	}

	public function settings(): void
	{
		$this->render(__FUNCTION__, [], 'show_settings');

		$saving = $this->isRequestSet('save');

		$this->adminService->configVars($saving);

		if ($saving)
			$this->adminService->redirect(AdminService::POST_URL . __FUNCTION__);
	}

	public function permissions(): void
	{
		$this->render(__FUNCTION__, [], 'show_settings');

		$saving = $this->isRequestSet('save');

		$this->adminService->permissionsConfigVars($saving);

		if ($saving)
			$this->adminService->redirect(AdminService::POST_URL . __FUNCTION__);
	}

	public function donate(): void
	{
		$this->render(__FUNCTION__);
	}

	public function moodList(): void
	{
		$this->adminService->isEnableFeature('mood', __FUNCTION__ . 'general');

		$this->render(__FUNCTION__, [
			Breeze::NAME => [
				'notice' => $this->getMessage(),
				'formId' => __FUNCTION__,
			],
		]);

		$start = $this->getRequest('start') ? : 0;
		$this->moodService->createMoodList([
			'id' => __FUNCTION__,
			'base_href' => $this->adminService->global('scripturl') .
				'?' . AdminService::POST_URL . __FUNCTION__,
		], $start);

		$toDeleteMoodIds = $this->getRequest('checked_icons');

		if ($this->isRequestSet('delete') &&
			!empty($toDeleteMoodIds))
		{
			$this->moodService->deleteMoods($toDeleteMoodIds);
			$this->adminService->redirect(AdminService::POST_URL . __FUNCTION__);
		}
	}

	public function render(string $subTemplate, array $params = [], string $smfTemplate = ''): void
	{
		$this->adminService->setSubActionContent($subTemplate, $params, $smfTemplate);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}
}
