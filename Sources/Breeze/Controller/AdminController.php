<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Breeze;
use Breeze\Service\AdminService;
use Breeze\Traits\PersistenceTrait as Persistence;

class Admin extends BaseController implements ControllerInterface
{
	use PersistenceTrait;

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
		$this->service->setSubActionContent(__FUNCTION__);
		$this->render('admin_home', [
		    'credits' => Breeze::credits(),
		]);
	}

	public function settings(): void
	{
		$scriptUrl = $this->adminService->global('scripturl');

		$this->service->setSubActionContent(__FUNCTION__);
		$this->render(__FUNCTION__, [
		    'post_url' => $scriptUrl . '?' . AdminService::POST_URL . __FUNCTION__ . ';save',
		]);

		$this->adminService->configVars();

		if ($this->request->get('save'))
		{
			$this->adminService->saveConfigVars();
			$this->adminService->redirect(AdminService::POST_URL . __FUNCTION__);
		}
	}

	public function permissions(): void
	{
		$scriptUrl = $this->adminService->global('scripturl');

		$this->service->setSubActionContent(__FUNCTION__);
		$this->render(__FUNCTION__, [
		    'post_url' => $scriptUrl . '?' . AdminService::POST_URL . __FUNCTION__ . ';save',
		]);

		$this->adminService->permissionsConfigVars();

		if ($this->request->get('save'))
		{
			$this->adminService->saveConfigVars();
			$this->adminService->redirect( AdminService::POST_URL . __FUNCTION__ );
		}
	}

	public function moodList()
	{
		$this->service->isEnableFeature('mood', __FUNCTION__ . 'general');

		$this->service->setSubActionContent(__FUNCTION__);
		$this->render(__FUNCTION__, [
		    'notice' => $this->getMessage(),
		]);

		$this->service->setSubActionContent(__FUNCTION__);

		$start = $this->request->get('start');

		$this->service->showMoodList( __FUNCTION__, $start);


		// So, are we deleting?
		if ($data->get('delete') && $data->get('checked_icons'))
		{
			// Get the icons to delete.
			$toDelete = $data->get('checked_icons');

			// They all are IDs right?
			$toDelete = array_map('intval', (array) $toDelete);

			// Call BreezeQuery here.
			$this->_app['query']->deleteMood($toDelete);

			// set a nice session message.
			$_SESSION['breeze'] = [
			    'message' => ['success_delete'],
			    'type' => 'info',
			];

			// Force a redirect.
			return redirectexit('action=admin;area=breezeadmin;sa=moodList');
		}
	}

	public function render(string $subTemplate, array $params): void
	{
		$context = $this->adminService->global('context');

		$context[$subTemplate] = $params;

		$this->adminService->setGlobal('context', $context);

		$this->adminService->setSubActionContent($subTemplate);
	}
}
