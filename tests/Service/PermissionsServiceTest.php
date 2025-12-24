<?php

declare(strict_types=1);

namespace Breeze\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PermissionsServiceTest extends TestCase
{
	private PermissionsService $permissionsService;

	public function setUp(): void
	{
		$this->permissionsService = new PermissionsService();
	}

	#[DataProvider('isAllowedToProvider')]
	public function testIsAllowedTo(string $permissionName, bool $expectedResult): void
	{
		$isAllowedTo = $this->permissionsService->isAllowedTo($permissionName);

		$this->assertEquals($expectedResult, $isAllowedTo);
	}

	public static function isAllowedToProvider(): array
	{
		return [
			'nope' => [
				'permissionName' => 'nope',
				'expectedResult' => false,
			],
			'yep' => [
				'permissionName' => 'yep',
				'expectedResult' => true,
			],
		];
	}

	#[DataProvider('permissionsProvider')]
	public function testPermissions(array $user_info, int $profileOwner, int $userPoster, array $expected): void
	{
		// Manually set the global user_info var
		$GLOBALS['user_info'] = $user_info;
		$result = $this->permissionsService->permissions($profileOwner, $userPoster);

		$this->assertEquals($expected, $result);
	}

	public static function permissionsProvider(): array
	{
		return [
			'guest user' => [
				'user_info' => ['is_guest' => true],
				'profileOwner' => 0,
				'userPoster' => 0,
				'expected' => [
					'Status' => [
						'edit' => false,
						'delete' => false,
						'post' => false,
					],
					'Comments' => [
						'edit' => false,
						'delete' => false,
						'post' => false,
					],
					'isEnable' => [
						'enableLikes' => false,
					],
					'Forum' => [
						'likesLike' => false,
						'adminForum' => false,
						'profileView' => false,
					],
				],
			],
			'profile owner can post' => [
				'user_info' => ['id' => 1, 'is_guest' => false],
				'profileOwner' => 1,
				'userPoster' => 1,
				'expected' => [
					'Status' => [
						'edit' => false,
						'delete' => true,
						'post' => true,
					],
					'Comments' => [
						'edit' => false,
						'delete' => true,
						'post' => true,
					],
					'isEnable' => [
						'enableLikes' => false,
					],
					'Forum' => [
						'likesLike' => false,
						'adminForum' => false,
						'profileView' => false,
					],
				],
			],
			'different users' => [
				'user_info' => ['id' => 2, 'is_guest' => false],
				'profileOwner' => 1,
				'userPoster' => 2,
				'expected' => [
					'Status' => [
						'edit' => false,
						'delete' => false,
						'post' => false,
					],
					'Comments' => [
						'edit' => false,
						'delete' => false,
						'post' => false,
					],
					'isEnable' => [
						'enableLikes' => false,
					],
					'Forum' => [
						'likesLike' => false,
						'adminForum' => false,
						'profileView' => false,
					],
				],
			],
		];
	}

	public function testIsFeatureEnable(): void
	{
		$result = $this->permissionsService->isFeatureEnable();

		$this->assertIsArray($result);
		$this->assertArrayHasKey('enableLikes', $result);
	}

	public function testForumPermissions(): void
	{
		$result = $this->permissionsService->forumPermissions();

		$this->assertIsArray($result);
		$this->assertArrayHasKey('adminForum', $result);
		$this->assertArrayHasKey('likesLike', $result);
	}

	#[DataProvider('hookPermissionsProvider')]
	public function testHookPermissions(array $initialGroups, array $initialList): void
	{
		$permissionGroups = $initialGroups;
		$permissionList = $initialList;

		$this->permissionsService->hookPermissions($permissionGroups, $permissionList);

		$this->assertArrayHasKey('membergroup', $permissionGroups);
		$this->assertArrayHasKey('simple', $permissionGroups['membergroup']);
		$this->assertArrayHasKey('classic', $permissionGroups['membergroup']);
		$this->assertContains('breeze_per_simple', $permissionGroups['membergroup']['simple']);
		$this->assertContains('breeze_per_classic', $permissionGroups['membergroup']['classic']);
	}

	public static function hookPermissionsProvider(): array
	{
		return [
			'empty arrays' => [
				'initialGroups' => [],
				'initialList' => [],
			],
			'existing data' => [
				'initialGroups' => ['membergroup' => ['existing' => ['test']]],
				'initialList' => ['membergroup' => ['existing_perm' => [true]]],
			],
		];
	}
}
