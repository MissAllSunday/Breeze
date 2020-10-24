<?php

declare(strict_types=1);


namespace Breeze\Breeze\Service;

use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Service\UserService;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
	/**
	 * @var MockBuilder|UserRepositoryInterface
	 */
	private $userRepository;

	private UserService $userService;

	public function setUp(): void
	{
		$this->userRepository = $this->createMock(UserRepositoryInterface::class);

		$this->userService = new UserService($this->userRepository);
	}

	public function testGetCurrentUserInfo(): void
	{
		$user_info = $this->userService->getCurrentUserInfo();

		$this->assertArrayHasKey('id', $user_info);
		$this->assertEquals(666, $user_info['id']);
	}

	public function testGetCurrentUserSettings(): void
	{
		$this->userRepository
			->expects($this->once())
			->method('getUserSettings')
			->with(666)
			->willReturn([
				'generalWall' => 1
			]);

		$currentUserSettings = $this->userService->getCurrentUserSettings();

		$this->assertEquals([
			'generalWall' => 1
		], $currentUserSettings);
	}

	/**
	 * @dataProvider getUserSettings
	 */
	public function testGetUserSettings(array $userSettings, int $userId): void
	{
		$this->userRepository
			->expects($this->once())
			->method('getUserSettings')
			->with($userId)
			->willReturn($userSettings);

		$currentUserSettings = $this->userService->getUserSettings($userId);

		$this->assertEquals($userSettings, $currentUserSettings);
	}

	public function getUserSettings(): array
	{
		return [
			'happy happy joy joy' => [
				'userSettings' => [
					'generalWall' => 1
				],
				'userId' => 666,
			],
			'not found' => [
				'userSettings' => [],
				'userId' => 1,
			],
		];
	}

	protected function getMockInstance(string $class): MockObject
	{
		return $this->getMockBuilder($class)
			->disableOriginalConstructor()
			->getMock();
	}
}
