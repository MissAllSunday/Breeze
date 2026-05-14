<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Service\BuddyServiceInterface;
use Breeze\Util\Response;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class BuddyControllerTest extends TestCase
{
	private BuddyServiceInterface|MockObject $buddyService;

	private Response|MockObject $response;

	private BuddyController $controller;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->buddyService = $this->createMock(BuddyServiceInterface::class);
		$this->response = $this->createMock(Response::class);

		$_REQUEST['u'] = 5;
		$_SERVER['HTTP_X_SMF_AJAX'] = '1';

		$GLOBALS['user_info'] = [
			'id' => 10,
			'is_guest' => false,
			'buddies' => [],
		];

		$this->buddyService->method('getBuddyStatusForUsers')->willReturn([]);

		$this->controller = new BuddyController(
			$this->response,
			$this->buddyService
		);
	}

	protected function tearDown(): void
	{
		unset($_REQUEST['u']);
		unset($_SERVER['HTTP_X_SMF_AJAX']);
	}

	public function testHandleDelegatesToAddBuddy(): void
	{
		$this->buddyService->expects($this->once())
			->method('addBuddy')
			->with(5, $this->callback(function (array $userInfo) {
				return $userInfo['id'] === 10;
			}));

		$this->controller->handle();
	}

	public function testHandleDelegatesToRemoveBuddyWhenAlreadyBuddies(): void
	{
		$GLOBALS['user_info']['buddies'] = [5];

		$this->controller = new BuddyController(
			$this->response,
			$this->buddyService
		);

		$this->buddyService->expects($this->once())
			->method('removeBuddy')
			->with(5, $this->callback(function (array $userInfo) {
				return $userInfo['id'] === 10;
			}));

		$this->buddyService->expects($this->never())
			->method('addBuddy');

		$this->controller->handle();
	}

	public function testConfirmDelegatesToConfirmBuddy(): void
	{
		$this->buddyService->expects($this->once())
			->method('confirmBuddy')
			->with(5, $this->callback(function (array $userInfo) {
				return $userInfo['id'] === 10;
			}));

		$this->controller->confirm();
	}

	public function testGetSubActions(): void
	{
		$this->assertEquals(['handle', 'confirm', 'decline', 'requests'], $this->controller->getSubActions());
	}

	public function testGetMainAction(): void
	{
		$this->assertEquals('handle', $this->controller->getMainAction());
	}

	public function testGetActionName(): void
	{
		$this->assertEquals('handle', $this->controller->getActionName());
	}
}
