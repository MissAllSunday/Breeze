<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Breeze;
use Breeze\Service\AdminService;
use Breeze\Service\Request;

class Admin extends BaseController implements ControllerInterface
{
	public const SUB_ACTIONS = [
		'general',
		'settings',
		'permissions',
		'cover',
		'donate',
		'moodList',
		'moodEdit',
	];

	/**
	 * @var AdminService
	 */
	protected $adminService;

	public function dispatch(): void
    {
        $this->service->initSettingsPage($this->getSubActions());

        $this->subActionCall();
    }

	public function general(): void
	{
		$this->render('admin_home', [
			'credits' => Breeze::credits(),
		]);
	}

	public function settings(): void
	{
		$this->adminService->requireOnce('ManageServer');
		$scriptUrl = $this->adminService->global('scripturl');

		$this->render('settings', [
			'post_url' => $scriptUrl . '?' . AdminService::POST_URL . ';save',
		]);

		$this->adminService->configVars();

		if ($this->request->get('save'))
			$this->adminService->saveConfigVars();
	}

	public function render(string $subTemplate, array $params): void
	{
		$context = $this->adminService->global('context');

		$context[$subTemplate] = $params;

		$this->adminService->setGlobal('context', $context);

		$this->adminService->setSubActionContent($subTemplate);
	}
}
