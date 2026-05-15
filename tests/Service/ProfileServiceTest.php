<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\User\SettingsRepositoryInterface;
use Breeze\Util\Components;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class ProfileServiceTest extends TestCase
{
	private SettingsRepositoryInterface|MockObject $userSettingsRepository;

	private Components|MockObject $components;

	private PermissionsServiceInterface|MockObject $permissionsService;

	private ProfileService $profileService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->userSettingsRepository = $this->createMock(SettingsRepositoryInterface::class);
		$this->components = $this->createStub(Components::class);
		$this->permissionsService = $this->createStub(PermissionsServiceInterface::class);

		$this->profileService = new ProfileService(
			$this->userSettingsRepository,
			$this->components,
			$this->permissionsService,
		);
	}

	public function testGetUserSettings(): void
	{
		$userId = 123;
		$expectedSettings = UserSettingsEntity::from(['wall' => $userId]);

		$this->userSettingsRepository
			->expects($this->once())
			->method('getById')
			->with($this->equalTo($userId))
			->willReturn($expectedSettings);

		$result = $this->profileService->getUserSettings($userId);

		$this->assertEquals($expectedSettings, $result);
	}

	#[DataProvider('isAllowedToSeePageProvider')]
	public function testIsAllowedToSeePage(
		UserSettingsEntity $profileSettings,
		bool $forceWall,
		bool $isAllowedTo,
		bool $expected
	): void {
		$this->profileService = $this->getMockBuilder(ProfileService::class)
			->setConstructorArgs([
				$this->userSettingsRepository,
				$this->components,
				$this->permissionsService,
			])
			->onlyMethods(['getSetting', 'isAllowedTo'])
			->getMock();

		$this->profileService->method('getSetting')->willReturn($forceWall);
		$this->profileService->method('isAllowedTo')->willReturn($isAllowedTo);

		$result = $this->profileService->isAllowedToSeePage($profileSettings);

		$this->assertEquals($expected, $result);
	}

	public static function isAllowedToSeePageProvider(): array
	{
		return [
			'wall enabled and allowed' => [
				'profileSettings' => UserSettingsEntity::from(['wall' => true]),
				'forceWall' => false,
				'isAllowedTo' => true,
				'expected' => true,
			],
			'wall disabled but force wall enabled' => [
				'profileSettings' => UserSettingsEntity::from(['wall' => false]),
				'forceWall' => true,
				'isAllowedTo' => true,
				'expected' => true,
			],
			'wall disabled and no force wall' => [
				'profileSettings' => UserSettingsEntity::from(['wall' => false]),
				'forceWall' => false,
				'isAllowedTo' => true,
				'expected' => false,
			],
		];
	}

	#[DataProvider('stalkingCheckProvider')]
	public function testStalkingCheck(array $userInfo, UserSettingsEntity $stalkedSettings, bool $expected): void
	{
		$this->profileService = $this->getMockBuilder(ProfileService::class)
			->setConstructorArgs([
				$this->userSettingsRepository,
				$this->components,
				$this->permissionsService,
			])
			->onlyMethods(['global'])
			->getMock();

		$this->profileService->method('global')->willReturn($userInfo);
		$this->userSettingsRepository->method('getById')->willReturn($stalkedSettings);

		$result = $this->profileService->stalkingCheck(456);

		$this->assertEquals($expected, $result);
	}

	public static function stalkingCheckProvider(): array
	{
		return [
			'user is blocked' => [
				'userInfo' => ['id' => 123],
				'stalkedSettings' => UserSettingsEntity::from([
					UserSettingsEntity::KICK_IGNORED => true,
					UserSettingsEntity::BLOCK_LIST => '123']),
				'expected' => true,
			],
			'user is not blocked' => [
				'userInfo' => ['id' => 123],
				'stalkedSettings' => UserSettingsEntity::from([
					UserSettingsEntity::KICK_IGNORED => true,
					UserSettingsEntity::BLOCK_LIST => '456, 789',
				]),
				'expected' => false,
			],
		];
	}

	#[DataProvider('canShowAddBuddyButtonProvider')]
	public function testCanShowAddBuddyButton(
		int $profileId,
		int $userId,
		array $userBuddies,
		UserSettingsEntity $wallUserSettings,
		bool $expected
	): void {
		$this->profileService = $this->getMockBuilder(ProfileService::class)
			->setConstructorArgs([
				$this->userSettingsRepository,
				$this->components,
				$this->permissionsService,
			])
			->onlyMethods(['getCurrentUserInfo'])
			->getMock();

		$this->profileService->method('getCurrentUserInfo')->willReturn([
			'id' => $userId,
			'buddies' => $userBuddies,
		]);

		$result = $this->profileService->canShowAddBuddyButton(
			$profileId,
			$userId,
			$wallUserSettings
		);

		$this->assertEquals($expected, $result);
	}

	public static function canShowAddBuddyButtonProvider(): array
	{
		return [
			'own wall - hide button' => [
				'profileId' => 1,
				'userId' => 1,
				'userBuddies' => [],
				'wallUserSettings' => UserSettingsEntity::from([]),
				'expected' => false,
			],
			'already buddy - hide button' => [
				'profileId' => 2,
				'userId' => 1,
				'userBuddies' => [2, 3, 4],
				'wallUserSettings' => UserSettingsEntity::from([]),
				'expected' => false,
			],
			'already buddy with string ids - hide button' => [
				'profileId' => 2,
				'userId' => 1,
				'userBuddies' => ['2', '3', '4'],
				'wallUserSettings' => UserSettingsEntity::from([]),
				'expected' => false,
			],
			'in ignore list with block enabled - hide button' => [
				'profileId' => 2,
				'userId' => 1,
				'userBuddies' => [],
				'wallUserSettings' => UserSettingsEntity::from([
					UserSettingsEntity::BLOCK_LIST => '1,5,10',
					UserSettingsEntity::BLOCK_BUDDY_REQUESTS => 1,
				]),
				'expected' => false,
			],
			'in ignore list with block disabled - show button' => [
				'profileId' => 2,
				'userId' => 1,
				'userBuddies' => [],
				'wallUserSettings' => UserSettingsEntity::from([
					UserSettingsEntity::BLOCK_LIST => '1,5,10',
					UserSettingsEntity::BLOCK_BUDDY_REQUESTS => 0,
				]),
				'expected' => true,
			],
			'not in ignore list - show button' => [
				'profileId' => 2,
				'userId' => 1,
				'userBuddies' => [],
				'wallUserSettings' => UserSettingsEntity::from([
					UserSettingsEntity::BLOCK_LIST => '5,10',
					UserSettingsEntity::BLOCK_BUDDY_REQUESTS => 1,
				]),
				'expected' => true,
			],
			'empty ignore list with block enabled - show button' => [
				'profileId' => 2,
				'userId' => 1,
				'userBuddies' => [],
				'wallUserSettings' => UserSettingsEntity::from([
					UserSettingsEntity::BLOCK_BUDDY_REQUESTS => 1,
				]),
				'expected' => true,
			],
			'valid buddy request candidate - show button' => [
				'profileId' => 2,
				'userId' => 1,
				'userBuddies' => [3, 4, 5],
				'wallUserSettings' => UserSettingsEntity::from([
					UserSettingsEntity::BLOCK_LIST => '10,20',
					UserSettingsEntity::BLOCK_BUDDY_REQUESTS => 1,
				]),
				'expected' => true,
			],
		];
	}

	public function testLoadUsersInfoEnrichesWithBreezeSettings(): void
	{
		$this->userSettingsRepository
			->method('loadUsersInfo')
			->willReturn([
				2 => [
					'id' => 2,
					'name' => 'Test User',
					'avatar' => ['href' => 'avatar.png'],
				],
			]);

		$settings = UserSettingsEntity::from([
			UserSettingsEntity::BLOCK_LIST => '1,5',
			UserSettingsEntity::BLOCK_BUDDY_REQUESTS => 1,
		]);

		$this->userSettingsRepository
			->method('getByIds')
			->with([2])
			->willReturn([2 => $settings]);

		$result = $this->profileService->loadUsersInfo([2]);

		$this->assertEquals([1, 5], $result[2]['blockList']);
		$this->assertEquals(1, $result[2]['blockBuddyRequests']);
	}

	public function testLoadUsersInfoHandlesMissingSettingsGracefully(): void
	{
		$this->userSettingsRepository
			->method('loadUsersInfo')
			->willReturn([
				3 => [
					'id' => 3,
					'name' => 'Another User',
					'avatar' => ['href' => 'avatar.png'],
				],
			]);

		$this->userSettingsRepository
			->method('getByIds')
			->with([3])
			->willReturn([]);

		$result = $this->profileService->loadUsersInfo([3]);

		$this->assertEquals([], $result[3]['blockList']);
		$this->assertEquals(0, $result[3]['blockBuddyRequests']);
	}
}
