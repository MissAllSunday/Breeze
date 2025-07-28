<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\User\SettingsRepositoryInterface;
use Breeze\Util\Components;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
		$this->components = $this->createMock(Components::class);
		$this->permissionsService = $this->createMock(PermissionsServiceInterface::class);
		
		$this->profileService = new ProfileService(
			$this->userSettingsRepository,
			$this->components,
			$this->permissionsService
		);
	}

	public function testGetUserSettings(): void
	{
		$userId = 123;
		$expectedSettings = UserSettingsEntity::from(['id' => $userId]);

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
					'kick_ignored' => true,
					'blockList' => [123, 789],
				]),
				'expected' => true,
			],
			'user is not blocked' => [
				'userInfo' => ['id' => 123],
				'stalkedSettings' => UserSettingsEntity::from([
					'kick_ignored' => true,
					'blockList' => [456, 789],
				]),
				'expected' => false,
			],
		];
	}
}
