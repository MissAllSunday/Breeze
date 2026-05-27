<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Enums\PermissionsEnum;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
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
			'profile owner with different poster' => [
				'user_info' => ['id' => 1, 'is_guest' => false],
				'profileOwner' => 1,
				'userPoster' => 2,
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
			'poster owner with different profile' => [
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

	#[DataProvider('permissionsWithMockProvider')]
	public function testPermissionsWithControlledPermissions(
		array $user_info,
		int $profileOwner,
		int $userPoster,
		array $permissionsMap,
		array $expectedStatus,
		array $expectedComments,
	): void {
		$GLOBALS['user_info'] = $user_info;

		$mockService = $this->getMockBuilder(PermissionsService::class)
			->onlyMethods(['isAllowedTo'])
			->getMock();

		$mockService->method('isAllowedTo')
			->willReturnCallback(function (string $permission) use ($permissionsMap): bool {
				return $permissionsMap[$permission] ?? false;
			});

		$result = $mockService->permissions($profileOwner, $userPoster);

		$this->assertEquals($expectedStatus, $result['Status']);
		$this->assertEquals($expectedComments, $result['Comments']);
	}

	public static function permissionsWithMockProvider(): array
	{
		return [
			'non-owner with general delete permissions' => [
				'user_info' => ['id' => 3, 'is_guest' => false],
				'profileOwner' => 1,
				'userPoster' => 2,
				'permissionsMap' => [
					'deleteStatus' => true,
					'deleteComments' => true,
				],
				'expectedStatus' => [
					'edit' => false,
					'delete' => true,
					'post' => false,
				],
				'expectedComments' => [
					'edit' => false,
					'delete' => true,
					'post' => false,
				],
			],
			'non-owner with general post permissions' => [
				'user_info' => ['id' => 3, 'is_guest' => false],
				'profileOwner' => 1,
				'userPoster' => 2,
				'permissionsMap' => [
					'postStatus' => true,
					'postComments' => true,
				],
				'expectedStatus' => [
					'edit' => false,
					'delete' => false,
					'post' => true,
				],
				'expectedComments' => [
					'edit' => false,
					'delete' => false,
					'post' => true,
				],
			],
			'profile owner with profile delete false' => [
				'user_info' => ['id' => 1, 'is_guest' => false],
				'profileOwner' => 1,
				'userPoster' => 2,
				'permissionsMap' => [
					'deleteProfileStatus' => false,
					'deleteProfileComments' => false,
				],
				'expectedStatus' => [
					'edit' => false,
					'delete' => false,
					'post' => true,
				],
				'expectedComments' => [
					'edit' => false,
					'delete' => false,
					'post' => true,
				],
			],
			'poster owner with own delete true' => [
				'user_info' => ['id' => 2, 'is_guest' => false],
				'profileOwner' => 1,
				'userPoster' => 2,
				'permissionsMap' => [
					'deleteOwnStatus' => true,
					'deleteOwnComments' => true,
				],
				'expectedStatus' => [
					'edit' => false,
					'delete' => true,
					'post' => false,
				],
				'expectedComments' => [
					'edit' => false,
					'delete' => true,
					'post' => false,
				],
			],
			'poster owner with own delete false' => [
				'user_info' => ['id' => 2, 'is_guest' => false],
				'profileOwner' => 1,
				'userPoster' => 2,
				'permissionsMap' => [
					'deleteOwnStatus' => false,
					'deleteOwnComments' => false,
				],
				'expectedStatus' => [
					'edit' => false,
					'delete' => false,
					'post' => false,
				],
				'expectedComments' => [
					'edit' => false,
					'delete' => false,
					'post' => false,
				],
			],
			'profile owner and poster with own true profile false' => [
				'user_info' => ['id' => 1, 'is_guest' => false],
				'profileOwner' => 1,
				'userPoster' => 1,
				'permissionsMap' => [
					'deleteOwnStatus' => true,
					'deleteProfileStatus' => false,
					'deleteOwnComments' => true,
					'deleteProfileComments' => false,
				],
				'expectedStatus' => [
					'edit' => false,
					'delete' => true,
					'post' => true,
				],
				'expectedComments' => [
					'edit' => false,
					'delete' => true,
					'post' => true,
				],
			],
		];
	}

	public function testCanViewActivityReturnsFalseWithoutPermission(): void
	{
		// In the test environment viewGeneralWall is not granted → must return false.
		$result = $this->permissionsService->canViewActivity(0);

		$this->assertFalse($result);
	}

	public function testCanViewActivityReturnsTrueWhenPermissionGranted(): void
	{
		$mock = $this->getMockBuilder(PermissionsService::class)
			->onlyMethods(['isAllowedTo'])
			->getMock();

		$mock->method('isAllowedTo')
			->with(PermissionsEnum::VIEW_GENERAL_WALL)
			->willReturn(true);

		$this->assertTrue($mock->canViewActivity(1));
	}

	public function testCanViewProfileWallReturnsFalseWithoutPermission(): void
	{
		// In the test environment profile_view is not granted → must return false.
		$result = $this->permissionsService->canViewProfileWall(0);

		$this->assertFalse($result);
	}

	public function testCanViewProfileWallReturnsTrueWhenPermissionGranted(): void
	{
		$mock = $this->getMockBuilder(PermissionsService::class)
			->onlyMethods(['isAllowedTo'])
			->getMock();

		$mock->method('isAllowedTo')
			->with(PermissionsEnum::PROFILE_VIEW)
			->willReturn(true);

		$this->assertTrue($mock->canViewProfileWall(1));
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
