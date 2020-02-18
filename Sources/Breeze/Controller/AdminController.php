<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Service\Admin as AdminService;
use Breeze\Service\Request;

class Admin extends BaseController implements ControllerInterface
{
	/**
	 * @var AdminService
	 */
	private $adminService;

	public function __construct(Request $request, AdminService $adminService)
	{
		$this->request = $request;
		$this->adminService = $adminService;
	}

    public function dispatch(): void
    {
        $this->adminService->initSettingsPage($this->getSubActions());

        $this->subActionCall();
    }


	public function general(): void
	{
		$this->adminService->setGeneralPageContent();
	}

	public function render(string $subTemplate, array $params): void
	{
		// TODO: Implement render() method.
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
