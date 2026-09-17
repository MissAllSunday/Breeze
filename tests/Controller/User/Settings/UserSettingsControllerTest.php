<?php

declare(strict_types=1);

namespace Breeze\Controller\User\Settings;

use Breeze\Entity\UserSettingsEntity;
use Breeze\Enums\PermissionsEnum;
use Breeze\Repository\User\SettingsRepositoryInterface;
use Breeze\Service\SecurityServiceInterface;
use Breeze\Util\Form\UserSettingsBuilderInterface;
use Breeze\Util\ResponseInterface;
use Breeze\Util\Validate\InvalidDataException;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class UserSettingsControllerTest extends TestCase
{
	private const int CURRENT_USER_ID = 666;

	private const int OTHER_USER_ID = 456;

	private UserSettingsController | MockObject $userSettingsController;

	private SettingsRepositoryInterface | MockObject $userRepository;

	private ResponseInterface | MockObject $response;

	private UserSettingsBuilderInterface | MockObject $userSettingsBuilder;

	private SecurityServiceInterface | MockObject $securityService;

	private ValidateActionsInterface | MockObject $validateActions;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$GLOBALS['context'] = [];
		$GLOBALS['user_info'] = ['id' => self::CURRENT_USER_ID, 'is_guest' => false];

		$this->userRepository = $this->createMock(SettingsRepositoryInterface::class);
		$this->response = $this->createMock(ResponseInterface::class);
		$this->userSettingsBuilder = $this->createMock(UserSettingsBuilderInterface::class);
		$this->securityService = $this->createMock(SecurityServiceInterface::class);
		$this->validateActions = $this->createMock(ValidateActionsInterface::class);

		$this->userSettingsController = $this->buildController(false);
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
		$GLOBALS['user_info'] = ['id' => self::CURRENT_USER_ID, 'is_guest' => false];
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

	/**
	 * Partial mock so `isAllowedTo()` can be driven per test; the bootstrap
	 * permission map hardcodes `admin_forum` to false, which would make the
	 * administrator fallback untestable.
	 *
	 * @throws Exception
	 */
	private function buildController(bool $isAdmin): UserSettingsController | MockObject
	{
		$controller = $this->getMockBuilder(UserSettingsController::class)
			->setConstructorArgs([
				$this->userRepository,
				$this->response,
				$this->userSettingsBuilder,
				$this->securityService,
				$this->validateActions,
			])
			->onlyMethods(['isAllowedTo'])
			->getMock();

		$controller->method('isAllowedTo')
			->with(PermissionsEnum::ADMIN_FORUM)
			->willReturn($isAdmin);

		return $controller;
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
		$userId = self::CURRENT_USER_ID;
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
	}

	/**
	 * IDOR regression: rendering another member's settings form must be denied
	 * and must not leak their stored options.
	 */
	public function testMainDeniesAccessToAnotherMembersSettings(): void
	{
		$_REQUEST['u'] = self::OTHER_USER_ID;
		$GLOBALS['scripturl'] = 'https://example.com/';

		$this->userRepository->expects($this->never())->method('getById');
		$this->userSettingsBuilder->expects($this->never())->method('setForm');
		$this->securityService->expects($this->never())->method('createToken');

		$this->expectException(\Error::class);
		$this->expectExceptionMessage('Breeze_error_no_access');

		$this->userSettingsController->main();
	}

	public function testMainAllowsAdminForumFallback(): void
	{
		$this->userSettingsController = $this->buildController(true);

		$_REQUEST['u'] = self::OTHER_USER_ID;
		$GLOBALS['scripturl'] = 'https://example.com/';

		$userSettings = $this->createMock(UserSettingsEntity::class);
		$userSettings->method('toArray')->willReturn(['wall' => 1]);

		$this->userRepository->expects($this->once())
			->method('getById')
			->with(self::OTHER_USER_ID)
			->willReturn($userSettings);

		$this->userSettingsBuilder->expects($this->once())->method('setForm');
		$this->userSettingsBuilder->expects($this->once())->method('display');

		$this->userSettingsController->main();
	}

	public function testSave(): void
	{
		$userId = self::CURRENT_USER_ID;
		$scriptUrl = 'https://example.com/';
		$userSettings = [
			'wall' => 1,
			'generalWall' => 1,
			'paginationNumber' => 10,
		];

		$_REQUEST['u'] = $userId;
		$_REQUEST['user_settings'] = $userSettings;
		$GLOBALS['scripturl'] = $scriptUrl;

		$this->validateActions->expects($this->once())
			->method('setUp')
			->with($userSettings, UserSettingsController::ACTION_SAVE);

		$this->validateActions->expects($this->once())->method('isValid');

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
	}

	/**
	 * IDOR regression: the core defect. A valid CSRF token is not
	 * authorization — writing another member's settings must be refused before
	 * anything reaches the repository.
	 */
	public function testSaveDeniesWriteToAnotherMembersSettings(): void
	{
		$_REQUEST['u'] = self::OTHER_USER_ID;
		$_REQUEST['user_settings'] = ['wall' => 1];
		$GLOBALS['scripturl'] = 'https://example.com/';

		$this->validateActions->expects($this->never())->method('setUp');
		$this->validateActions->expects($this->never())->method('isValid');
		$this->userRepository->expects($this->never())->method('insert');
		$this->response->expects($this->never())->method('redirect');

		$this->expectException(\Error::class);
		$this->expectExceptionMessage('Breeze_error_no_access');

		$this->userSettingsController->save();
	}

	public function testSaveAllowsAdminForumFallback(): void
	{
		$this->userSettingsController = $this->buildController(true);

		$userSettings = ['wall' => 1, 'generalWall' => 1];

		$_REQUEST['u'] = self::OTHER_USER_ID;
		$_REQUEST['user_settings'] = $userSettings;
		$GLOBALS['scripturl'] = 'https://example.com/';

		$this->validateActions->expects($this->once())->method('isValid');

		$this->userRepository->expects($this->once())
			->method('insert')
			->with($userSettings, self::OTHER_USER_ID)
			->willReturn(true);

		$this->response->expects($this->once())->method('redirect');

		$this->userSettingsController->save();
	}

	/**
	 * A missing or malformed payload must not be forwarded to the repository;
	 * it is normalized to an empty array so `insert()` can merge defaults.
	 */
	public function testSaveNormalizesMissingPayload(): void
	{
		$_REQUEST['u'] = self::CURRENT_USER_ID;
		unset($_REQUEST['user_settings']);
		$GLOBALS['scripturl'] = 'https://example.com/';

		$this->validateActions->expects($this->once())
			->method('setUp')
			->with([], UserSettingsController::ACTION_SAVE);

		$this->userRepository->expects($this->once())
			->method('insert')
			->with([], self::CURRENT_USER_ID)
			->willReturn(true);

		$this->userSettingsController->save();
	}

	/**
	 * Validation must gate the write: a rejected payload never reaches the
	 * repository and never redirects.
	 */
	public function testSaveRejectsInvalidPayload(): void
	{
		$_REQUEST['u'] = self::CURRENT_USER_ID;
		$_REQUEST['user_settings'] = ['wall' => 1];
		$GLOBALS['scripturl'] = 'https://example.com/';

		$this->validateActions->method('isValid')
			->willThrowException(new InvalidDataException('incomplete_data'));

		$this->userRepository->expects($this->never())->method('insert');
		$this->response->expects($this->never())->method('redirect');

		$this->expectException(\Error::class);
		$this->expectExceptionMessage('Breeze_error_incomplete_data');

		$this->userSettingsController->save();
	}
}
