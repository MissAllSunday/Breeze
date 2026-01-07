<?php

declare(strict_types=1);

namespace Breeze\Controller\User\Settings;

use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\User\SettingsRepositoryInterface;
use Breeze\Util\Form\UserSettingsBuilderInterface;
use Breeze\Util\Response;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class UserSettingsControllerTest extends TestCase
{
	private UserSettingsController $userSettingsController;

	private SettingsRepositoryInterface | MockObject $userRepository;

	private Response | MockObject $response;

	private UserSettingsBuilderInterface | MockObject $userSettingsBuilder;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$GLOBALS['context'] = [];

		$this->userRepository = $this->createMock(SettingsRepositoryInterface::class);
		$this->response = $this->createMock(Response::class);
		$this->userSettingsBuilder = $this->createMock(UserSettingsBuilderInterface::class);

		$this->userSettingsController = new UserSettingsController(
			$this->userRepository,
			$this->response,
			$this->userSettingsBuilder
		);
	}

	protected function tearDown(): void
	{
		// Restore global state to bootstrap values
		$GLOBALS['context'] = [
			'session_var' => 'foo',
			'session_id' => 'baz',
			'cust_profile_fields_placement' => [
				'standard',
				'icons',
				'above_signature',
				'below_signature',
				'below_avatar',
				'above_member',
				'bottom_poster',
				'before_member',
				'after_member',
			],
		];
		$GLOBALS['scripturl'] = 'localhost';
		unset($_REQUEST['u']);
		unset($_REQUEST['user_settings']);
		$_SESSION['Breeze'] = [
			'notice' => [
				'message' => 'Kaizoku ou ni ore wa naru',
				'type' => 'info',
			],
			'flood_1' => [
				'time' => time() + 10,
				'msgCount' => 0,
			],
			'flood_2' => [
				'time' => time() + 10,
				'msgCount' => 666,
			],
			'flood_3' => [
				'time' => time() - 10,
				'msgCount' => 666,
			],
			'flood_4' => [
				'time' => time() - 10,
				'msgCount' => 3,
			],
			'flood_5' => [],
		];
	}

	public function testGetSubActions(): void
	{
		$this->assertEquals(UserSettingsController::SUB_ACTIONS, $this->userSettingsController->getSubActions());
	}

	public function testGetMainAction(): void
	{
		$this->assertEquals(UserSettingsController::ACTION_MAIN, $this->userSettingsController->getMainAction());
	}

	public function testGetActionName(): void
	{
		$this->assertEquals(UserSettingsController::ACTION_MAIN, $this->userSettingsController->getActionName());
	}

	public function testMain(): void
	{
		$userId = 123;
		$scriptUrl = 'https://example.com/';
		$formHtml = '<form>Test Form</form>';

		$_REQUEST['u'] = $userId;
		$GLOBALS['scripturl'] = $scriptUrl;

		$userSettings = $this->createMock(UserSettingsEntity::class);
		$userSettings->method('toArray')->willReturn([
			'wall' => 1,
			'generalWall' => 0,
			'paginationNumber' => 5,
		]);

		$this->userRepository->expects($this->once())
			->method('getById')
			->with($userId)
			->willReturn($userSettings);

		$this->userSettingsBuilder->expects($this->once())
			->method('setForm')
			->with(
				$this->callback(function ($formOptions) use ($scriptUrl, $userId) {
					return isset($formOptions['name'])
						&& $formOptions['name'] === UserSettingsEntity::IDENTIFIER
						&& isset($formOptions['url'])
						&& str_contains($formOptions['url'], $scriptUrl)
						&& str_contains($formOptions['url'], 'u=' . $userId);
				}),
				$this->callback(function ($formValues) {
					return isset($formValues['wall']);
				})
			);

		$this->userSettingsBuilder->expects($this->once())
			->method('display')
			->willReturn($formHtml);

		$this->userSettingsController->main();

		// Clean up
		unset($_REQUEST['u']);
		unset($GLOBALS['scripturl']);
	}

	public function testSave(): void
	{
		$userId = 456;
		$scriptUrl = 'https://example.com/';
		$userSettings = [
			'wall' => 1,
			'generalWall' => 1,
			'paginationNumber' => 10,
		];

		$_REQUEST['u'] = $userId;
		$_REQUEST['user_settings'] = $userSettings;
		$GLOBALS['scripturl'] = $scriptUrl;

		$this->userRepository->expects($this->once())
			->method('insert')
			->with($userSettings, $userId)
			->willReturn(true);

		$this->response->expects($this->once())
			->method('redirect')
			->with($this->callback(function ($url) use ($scriptUrl, $userId) {
				return str_contains($url, $scriptUrl)
					&& str_contains($url, 'u=' . $userId)
					&& str_contains($url, 'sa=' . UserSettingsController::ACTION_MAIN);
			}));

		$this->userSettingsController->save();

		// Clean up
		unset($_REQUEST['u']);
		unset($_REQUEST['user_settings']);
		unset($GLOBALS['scripturl']);
	}
}
