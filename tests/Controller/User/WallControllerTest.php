<?php

declare(strict_types=1);

namespace Breeze\Controller\User;

use Breeze\Entity\UserSettingsEntity;
use Breeze\Service\ProfileServiceInterface;
use Breeze\Util\Response;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class WallControllerTest extends TestCase
{
	private WallController $wallController;

	private Response | MockObject $response;

	private ProfileServiceInterface | MockObject $profileService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$GLOBALS['context'] = [];

		$this->response = $this->createMock(Response::class);
		$this->profileService = $this->createMock(ProfileServiceInterface::class);

		$this->wallController = new WallController(
			$this->response,
			$this->profileService
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
		unset($_REQUEST['u']);
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
		$this->assertEquals(WallController::SUB_ACTIONS, $this->wallController->getSubActions());
	}

	public function testGetMainAction(): void
	{
		$this->assertEquals(WallController::ACTION_WALL, $this->wallController->getMainAction());
	}

	public function testGetActionName(): void
	{
		$this->assertEquals(WallController::ACTION_WALL, $this->wallController->getActionName());
	}

	public function testGetActionVarName(): void
	{
		$this->assertEquals('action', $this->wallController->getActionVarName());
	}

	public function testWall(): void
	{
		$userId = 123;
		$GLOBALS['user_info'] = ['id' => $userId];

		$this->profileService->expects($this->once())
			->method('setEditor');

		$this->profileService->expects($this->once())
			->method('loadComponents')
			->with($userId);

		$this->wallController->wall();
	}

	public function testProfileWithValidUser(): void
	{
		$profileId = 456;
		$currentUserId = 123;
		$_REQUEST['u'] = $profileId;

		$profileSettings = $this->createMock(UserSettingsEntity::class);
		$profileSettings->method('getBuddies')->willReturn([]);
		$profileSettings->method('getEnableBuddiesTab')->willReturn(0);

		$currentUserInfo = ['id' => $currentUserId];

		$this->profileService->expects($this->once())
			->method('getUserSettings')
			->with($profileId)
			->willReturn($profileSettings);

		$this->profileService->expects($this->once())
			->method('getCurrentUserInfo')
			->willReturn($currentUserInfo);

		$this->profileService->expects($this->once())
			->method('isAllowedToSeePage')
			->with($profileSettings, $profileId, $currentUserId)
			->willReturn(true);

		$this->profileService->expects($this->once())
			->method('setEditor');

		$this->profileService->expects($this->once())
			->method('loadComponents')
			->with($profileId);

		$this->wallController->profile();

		// Clean up
		unset($_REQUEST['u']);
	}

	public function testProfileWithEmptyProfileId(): void
	{
		$_REQUEST['u'] = 0;

		$this->expectException(\Error::class);

		$this->wallController->profile();

		// Clean up
		unset($_REQUEST['u']);
	}

	public function testProfileWithBuddiesTab(): void
	{
		$profileId = 456;
		$currentUserId = 123;
		$buddies = [1, 2, 3];
		$buddiesData = [
			1 => ['name' => 'User 1'],
			2 => ['name' => 'User 2'],
			3 => ['name' => 'User 3'],
		];

		$_REQUEST['u'] = $profileId;

		$profileSettings = $this->createMock(UserSettingsEntity::class);
		$profileSettings->method('getBuddies')->willReturn($buddies);
		$profileSettings->method('getEnableBuddiesTab')->willReturn(1);

		$currentUserInfo = ['id' => $currentUserId];

		$this->profileService->expects($this->once())
			->method('getUserSettings')
			->with($profileId)
			->willReturn($profileSettings);

		$this->profileService->expects($this->once())
			->method('getCurrentUserInfo')
			->willReturn($currentUserInfo);

		$this->profileService->expects($this->once())
			->method('isAllowedToSeePage')
			->with($profileSettings, $profileId, $currentUserId)
			->willReturn(true);

		$this->profileService->expects($this->once())
			->method('loadUsersInfo')
			->with($buddies)
			->willReturn($buddiesData);

		$this->profileService->expects($this->once())
			->method('setEditor');

		$this->profileService->expects($this->once())
			->method('loadComponents')
			->with($profileId);

		$this->wallController->profile();

		// Clean up
		unset($_REQUEST['u']);
	}

	public function testProfileNotAllowedToSeePage(): void
	{
		$profileId = 456;
		$currentUserId = 123;
		$_REQUEST['u'] = $profileId;

		$profileSettings = $this->createMock(UserSettingsEntity::class);
		$currentUserInfo = ['id' => $currentUserId];

		$this->profileService->expects($this->once())
			->method('getUserSettings')
			->with($profileId)
			->willReturn($profileSettings);

		$this->profileService->expects($this->once())
			->method('getCurrentUserInfo')
			->willReturn($currentUserInfo);

		$this->profileService->expects($this->once())
			->method('isAllowedToSeePage')
			->with($profileSettings, $profileId, $currentUserId)
			->willReturn(false);

		$this->expectException(\Error::class);

		$this->wallController->profile();

		// Clean up
		unset($_REQUEST['u']);
	}
}
