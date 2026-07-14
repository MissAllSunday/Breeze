<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Breeze;
use Breeze\Service\Actions\AdminServiceInterface;
use Breeze\Traits\PersistenceTrait;
use Breeze\Util\ResponseInterface;

class AdminController extends BaseController
{
	use PersistenceTrait;

	public const string ACTION_MAIN = 'main';
	public const string ACTION_SETTINGS = 'settings';
	public const string ACTION_PERMISSIONS = 'permissions';
	public const string ACTION_DONATE = 'donate';
	public const string ACTION_MAINTENANCE = 'maintenance';

	public const array SUB_ACTIONS = [
		self::ACTION_MAIN,
		self::ACTION_SETTINGS,
		self::ACTION_PERMISSIONS,
		self::ACTION_MAINTENANCE,
		self::ACTION_DONATE,
	];

	public function __construct(
		protected AdminServiceInterface $adminService,
		protected ResponseInterface $response
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

		$this->adminService->loadComponents();
	}

	public function settings(): void
	{
		$this->render(__FUNCTION__, [], 'show_settings');

		$saving = $this->isRequestSet('save');

		$this->adminService->configVars($saving);

		if ($saving) {
			$this->response->redirect(AdminServiceInterface::POST_URL . __FUNCTION__ . ';saved');
		}
	}

	public function permissions(): void
	{
		$this->render(__FUNCTION__, [], 'show_settings');

		$saving = $this->isRequestSet('save');

		$this->adminService->permissionsConfigVars($saving);

		if ($saving) {
			$this->response->redirect(AdminServiceInterface::POST_URL . __FUNCTION__ . ';saved');
		}
	}

	public function maintenance(): void
	{
		$this->render(__FUNCTION__);

		$fixing = $this->isRequestSet('fix');

		$this->adminService->maintenance($fixing);

		if ($fixing) {
			$this->response->redirect(AdminServiceInterface::POST_URL . __FUNCTION__ . ';saved');
		}
	}

	public function donate(): void
	{
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
