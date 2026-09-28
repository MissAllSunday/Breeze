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
	public function testPermissions(array $user_info, int $profileOwner, array $expected): void
	{
		// Manually set the global user_info var
		$GLOBALS['user_info'] = $user_info;
		$result = $this->permissionsService->permissions($profileOwner);

		$this->assertEquals($expected, $result);
	}

	public static function permissionsProvider(): array
	{
		return [
			'guest user' => [
				'user_info' => ['is_guest' => true],
				'profileOwner' => 0,
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
			'general wall: profileOwner=0 does not grant profile-owner rights' => [
				'user_info' => ['id' => 1, 'is_guest' => false],
				'profileOwner' => 0,
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
			'poster owner with different profile' => [
				'user_info' => ['id' => 2, 'is_guest' => false],
				'profileOwner' => 1,
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

		$result = $mockService->permissions($profileOwner);

		$this->assertEquals($expectedStatus, $result['Status']);
		$this->assertEquals($expectedComments, $result['Comments']);
	}

	/**
	 * Wall-level snapshot. `delete` here means "may delete ANY item on this
	 * wall" (profile-owner or blanket rights). Own-item rights are deliberately
	 * excluded: they are answered per item by canDelete(), and leaking them
	 * into the wall snapshot is what let every member delete everyone's
	 * content.
	 */
	public static function permissionsWithMockProvider(): array
	{
		return [
			'non-owner with general delete permissions' => [
				'user_info' => ['id' => 3, 'is_guest' => false],
				'profileOwner' => 1,
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
			'profile owner with profile delete true' => [
				'user_info' => ['id' => 1, 'is_guest' => false],
				'profileOwner' => 1,
				'permissionsMap' => [
					'deleteProfileStatus' => true,
					'deleteProfileComments' => true,
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
			// Regression: deleteOwn* must NOT surface as a wall-wide delete.
			'own delete rights do not leak into wall level delete' => [
				'user_info' => ['id' => 2, 'is_guest' => false],
				'profileOwner' => 1,
				'permissionsMap' => [
					'deleteOwnStatus' => true,
					'deleteOwnComments' => true,
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
			'non-owner without any delete permission' => [
				'user_info' => ['id' => 2, 'is_guest' => false],
				'profileOwner' => 1,
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
			// Regression: profile owner whose deleteProfile* is off keeps post
			// rights but gets no wall-wide delete, even with deleteOwn* on.
			'profile owner with own true and profile false' => [
				'user_info' => ['id' => 1, 'is_guest' => false],
				'profileOwner' => 1,
				'permissionsMap' => [
					'deleteOwnStatus' => true,
					'deleteProfileStatus' => false,
					'deleteOwnComments' => true,
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
			// The general wall has no single owner: profile-owner shortcuts
			// must never fire for profileOwner = 0.
			'general wall grants nothing without explicit permissions' => [
				'user_info' => ['id' => 1, 'is_guest' => false],
				'profileOwner' => 0,
				'permissionsMap' => [
					'deleteProfileStatus' => true,
					'deleteProfileComments' => true,
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
			// A member with id 0 (broken session) is treated as a guest.
			'member with zero id is treated as guest' => [
				'user_info' => ['id' => 0, 'is_guest' => false],
				'profileOwner' => 0,
				'permissionsMap' => [
					'deleteStatus' => true,
					'postStatus' => true,
					'deleteComments' => true,
					'postComments' => true,
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
		];
	}

	#[DataProvider('canDeleteProvider')]
	public function testCanDelete(
		array $user_info,
		string $type,
		int $authorId,
		int $wallOwnerId,
		array $permissionsMap,
		bool $expected,
	): void {
		$GLOBALS['user_info'] = $user_info;

		$mockService = $this->getMockBuilder(PermissionsService::class)
			->onlyMethods(['isAllowedTo'])
			->getMock();

		$mockService->method('isAllowedTo')
			->willReturnCallback(function (string $permission) use ($permissionsMap): bool {
				return $permissionsMap[$permission] ?? false;
			});

		$this->assertSame($expected, $mockService->canDelete($type, $authorId, $wallOwnerId));
	}

	/**
	 * canDelete() is the single authorization point for item deletion. The
	 * author and wall ids are always DB-derived, so a member holding only
	 * deleteOwn* can never delete someone else's content.
	 */
	public static function canDeleteProvider(): array
	{
		return [
			'author deletes own status' => [
				'user_info' => ['id' => 2, 'is_guest' => false],
				'type' => PermissionsEnum::TYPE_STATUS,
				'authorId' => 2,
				'wallOwnerId' => 1,
				'permissionsMap' => ['deleteOwnStatus' => true],
				'expected' => true,
			],
			// The core regression: deleteOwn* + someone else's content = deny.
			'author cannot delete another member status with own permission' => [
				'user_info' => ['id' => 2, 'is_guest' => false],
				'type' => PermissionsEnum::TYPE_STATUS,
				'authorId' => 5,
				'wallOwnerId' => 1,
				'permissionsMap' => ['deleteOwnStatus' => true],
				'expected' => false,
			],
			'wall owner deletes foreign status on own wall' => [
				'user_info' => ['id' => 1, 'is_guest' => false],
				'type' => PermissionsEnum::TYPE_STATUS,
				'authorId' => 5,
				'wallOwnerId' => 1,
				'permissionsMap' => ['deleteProfileStatus' => true],
				'expected' => true,
			],
			'wall owner without profile permission is denied' => [
				'user_info' => ['id' => 1, 'is_guest' => false],
				'type' => PermissionsEnum::TYPE_STATUS,
				'authorId' => 5,
				'wallOwnerId' => 1,
				'permissionsMap' => ['deleteOwnStatus' => true],
				'expected' => false,
			],
			'moderator deletes anything' => [
				'user_info' => ['id' => 9, 'is_guest' => false],
				'type' => PermissionsEnum::TYPE_COMMENTS,
				'authorId' => 5,
				'wallOwnerId' => 1,
				'permissionsMap' => ['deleteComments' => true],
				'expected' => true,
			],
			'author deletes own comment' => [
				'user_info' => ['id' => 2, 'is_guest' => false],
				'type' => PermissionsEnum::TYPE_COMMENTS,
				'authorId' => 2,
				'wallOwnerId' => 1,
				'permissionsMap' => ['deleteOwnComments' => true],
				'expected' => true,
			],
			'author cannot delete another member comment with own permission' => [
				'user_info' => ['id' => 2, 'is_guest' => false],
				'type' => PermissionsEnum::TYPE_COMMENTS,
				'authorId' => 5,
				'wallOwnerId' => 1,
				'permissionsMap' => ['deleteOwnComments' => true],
				'expected' => false,
			],
			'guest is always denied' => [
				'user_info' => ['id' => 0, 'is_guest' => true],
				'type' => PermissionsEnum::TYPE_STATUS,
				'authorId' => 0,
				'wallOwnerId' => 0,
				'permissionsMap' => [
					'deleteStatus' => true,
					'deleteOwnStatus' => true,
					'deleteProfileStatus' => true,
				],
				'expected' => false,
			],
			// Unknown ids (0) must never match the viewer, even when the viewer
			// holds every own/profile permission.
			'unknown author and wall ids are denied' => [
				'user_info' => ['id' => 2, 'is_guest' => false],
				'type' => PermissionsEnum::TYPE_STATUS,
				'authorId' => 0,
				'wallOwnerId' => 0,
				'permissionsMap' => [
					'deleteOwnStatus' => true,
					'deleteProfileStatus' => true,
				],
				'expected' => false,
			],
		];
	}

	#[DataProvider('canPostProvider')]
	public function testCanPost(
		array $user_info,
		string $type,
		int $wallOwnerId,
		array $permissionsMap,
		bool $expected,
	): void {
		$GLOBALS['user_info'] = $user_info;

		$mockService = $this->getMockBuilder(PermissionsService::class)
			->onlyMethods(['isAllowedTo'])
			->getMock();

		$mockService->method('isAllowedTo')
			->willReturnCallback(function (string $permission) use ($permissionsMap): bool {
				return $permissionsMap[$permission] ?? false;
			});

		$this->assertSame($expected, $mockService->canPost($type, $wallOwnerId));
	}

	public static function canPostProvider(): array
	{
		return [
			'wall owner posts on own wall without postStatus' => [
				'user_info' => ['id' => 1, 'is_guest' => false],
				'type' => PermissionsEnum::TYPE_STATUS,
				'wallOwnerId' => 1,
				'permissionsMap' => [],
				'expected' => true,
			],
			'visitor posts on foreign wall with postStatus' => [
				'user_info' => ['id' => 2, 'is_guest' => false],
				'type' => PermissionsEnum::TYPE_STATUS,
				'wallOwnerId' => 1,
				'permissionsMap' => ['postStatus' => true],
				'expected' => true,
			],
			'visitor cannot post on foreign wall without postStatus' => [
				'user_info' => ['id' => 2, 'is_guest' => false],
				'type' => PermissionsEnum::TYPE_STATUS,
				'wallOwnerId' => 1,
				'permissionsMap' => ['postComments' => true],
				'expected' => false,
			],
			'visitor comments on foreign wall with postComments' => [
				'user_info' => ['id' => 2, 'is_guest' => false],
				'type' => PermissionsEnum::TYPE_COMMENTS,
				'wallOwnerId' => 1,
				'permissionsMap' => ['postComments' => true],
				'expected' => true,
			],
			'visitor cannot comment on foreign wall without postComments' => [
				'user_info' => ['id' => 2, 'is_guest' => false],
				'type' => PermissionsEnum::TYPE_COMMENTS,
				'wallOwnerId' => 1,
				'permissionsMap' => ['postStatus' => true],
				'expected' => false,
			],
			// profileOwner = 0 is the general wall: no owner shortcut.
			'general wall requires postStatus' => [
				'user_info' => ['id' => 2, 'is_guest' => false],
				'type' => PermissionsEnum::TYPE_STATUS,
				'wallOwnerId' => 0,
				'permissionsMap' => [],
				'expected' => false,
			],
			'guest cannot post anywhere' => [
				'user_info' => ['id' => 0, 'is_guest' => true],
				'type' => PermissionsEnum::TYPE_STATUS,
				'wallOwnerId' => 1,
				'permissionsMap' => ['postStatus' => true],
				'expected' => false,
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
