<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Breeze;
use Breeze\Service\AdminService;
use Breeze\Service\Request;

class Admin extends BaseController implements ControllerInterface
{
	/**
	 * @var AdminService
	 */
	protected $adminService;

	public function __construct(Request $request, AdminService $service)
	{
		$this->request = $request;
		$this->adminService = $service;
	}

	public function dispatch(): void
    {
        $this->adminService->initSettingsPage($this->getSubActions());

        $this->subActionCall();
    }

	public function general(): void
	{
		$this->adminService->setSubActionContent();

		$this->render('admin_home', [
		    'credits' => Breeze::credits(),
		]);
	}

	public function settings()
	{
		$this->adminService->requireOnce('ManageServer');

		$this->render('settings', [

		]);

		if ($this->request->get('save'))
			$this->adminService->saveConfigVars();
	}

	public function render(string $subTemplate, array $params): void
	{
		$context = $this->adminService->global('context');

		$context['sub_template'] = $subTemplate;
		$context['page_title'] = \Breeze\Breeze::NAME . ' - ' . $this->_app['tools']->text('page_settings');
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => $context['page_title'],
			'description' => $this->_app['tools']->text('page_settings_desc'),
		];


		$context[$subTemplate] = $params;

		$this->adminService->setGlobal('context', $context);
	}

	public function getSubActions(): array
	{
		return [
		    'general',
		    'settings',
		    'permissions',
		    'cover',
		    'donate',
		    'moodList',
		    'moodEdit',
		];
	}
}
