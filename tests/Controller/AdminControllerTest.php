<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Service\Actions\AdminServiceInterface;
use Breeze\Util\ResponseInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class AdminControllerTest extends TestCase
{
	private AdminController $adminController;

	private AdminServiceInterface | MockObject $adminService;

	private ResponseInterface | MockObject $response;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->adminService = $this->createMock(AdminServiceInterface::class);
		$this->response = $this->createMock(ResponseInterface::class);

		$this->adminController = new AdminController(
			$this->adminService,
			$this->response
		);
	}

	public function testGetSubActions(): void
	{
		$this->assertEquals(AdminController::SUB_ACTIONS, $this->adminController->getSubActions());
	}

	public function testGetMainAction(): void
	{
		$this->assertEquals(AdminController::ACTION_MAIN, $this->adminController->getMainAction());
	}

	public function testGetActionName(): void
	{
		$this->assertEquals(AdminController::ACTION_MAIN, $this->adminController->getActionName());
	}

	public function testDispatch(): void
	{
		// Set up a valid sub-action in the request
		$_REQUEST['sa'] = 'donate';

		$this->adminService->expects($this->once())
			->method('init')
			->with(AdminController::SUB_ACTIONS);

		$this->adminService->expects($this->once())
			->method('defaultSubActionContent')
			->with('donate', [], '');

		$this->adminController->dispatch();

		// Clean up
		unset($_REQUEST['sa']);
	}

	public function testMain(): void
	{
		$this->adminService->expects($this->once())
			->method('defaultSubActionContent')
			->with(
				'main',
				$this->callback(function ($params) {
					return isset($params['Breeze']['credits'])
						&& isset($params['Breeze']['version'])
						&& isset($params['Breeze']['react']);
				}),
				''
			);

		$this->adminService->expects($this->once())
			->method('loadComponents');

		$this->adminController->main();
	}

	public function testSettingsWithoutSaving(): void
	{
		$this->adminService->expects($this->once())
			->method('defaultSubActionContent')
			->with('settings', [], 'show_settings');

		$this->adminService->expects($this->once())
			->method('configVars')
			->with(false);

		$this->response->expects($this->never())
			->method('redirect');

		$this->adminController->settings();
	}

	public function testSettingsWithSaving(): void
	{
		// Set up $_REQUEST to simulate save action
		$_REQUEST['save'] = '1';

		$this->adminService->expects($this->once())
			->method('defaultSubActionContent')
			->with('settings', [], 'show_settings');

		$this->adminService->expects($this->once())
			->method('configVars')
			->with(true);

		$this->response->expects($this->once())
			->method('redirect')
			->with(AdminServiceInterface::POST_URL . 'settings' . ';saved');

		$this->adminController->settings();

		// Clean up
		unset($_REQUEST['save']);
	}

	public function testPermissionsWithoutSaving(): void
	{
		$this->adminService->expects($this->once())
			->method('defaultSubActionContent')
			->with('permissions', [], 'show_settings');

		$this->adminService->expects($this->once())
			->method('permissionsConfigVars')
			->with(false);

		$this->response->expects($this->never())
			->method('redirect');

		$this->adminController->permissions();
	}

	public function testPermissionsWithSaving(): void
	{
		// Set up $_REQUEST to simulate save action
		$_REQUEST['save'] = '1';

		$this->adminService->expects($this->once())
			->method('defaultSubActionContent')
			->with('permissions', [], 'show_settings');

		$this->adminService->expects($this->once())
			->method('permissionsConfigVars')
			->with(true);

		$this->response->expects($this->once())
			->method('redirect')
			->with(AdminServiceInterface::POST_URL . 'permissions' . ';saved');

		$this->adminController->permissions();

		// Clean up
		unset($_REQUEST['save']);
	}

	public function testDonate(): void
	{
		$this->adminService->expects($this->once())
			->method('defaultSubActionContent')
			->with('donate', [], '');

		$this->adminController->donate();
	}
}
