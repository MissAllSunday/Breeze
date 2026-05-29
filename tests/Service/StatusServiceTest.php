<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\StatusEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Repository\User\SettingsRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\EmptyDataException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class StatusServiceTest extends TestCase
{
	private StatusRepositoryInterface | MockObject $statusRepository;

	private SettingsRepositoryInterface | MockObject $userRepository;

	private PermissionsServiceInterface | MockObject $permissionsService;

	private WallVisibilityServiceInterface | MockObject $wallVisibilityService;

	private MockObject|StatusService $statusService;

	/**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		$this->statusRepository = $this->createStub(StatusRepositoryInterface::class);
		$this->userRepository = $this->createStub(SettingsRepositoryInterface::class);
		$this->permissionsService = $this->createStub(PermissionsServiceInterface::class);
		$this->wallVisibilityService = $this->createMock(WallVisibilityServiceInterface::class);
		$this->statusService = $this->getMockBuilder(StatusService::class)
			->setConstructorArgs([$this->statusRepository,
				$this->userRepository,
				$this->permissionsService,
				$this->wallVisibilityService,
				null])
			->onlyMethods(['getCount'])
			->getMock();
	}

	/**
	 * @throws EmptyDataException
	 */
	#[DataProvider('getByProfileProvider')]
	public function testGetByProfile(int $wallId, array $expected): void
	{
		$this->userRepository->method('getById')->willReturn(UserSettingsEntity::from(['paginationNumber' => 5]));
		$this->permissionsService->method('permissions')->willReturn([
			'delete' => true,
			'edit' => false,
			'post' => true,
			'postComments' => true,
		]);
		$this->statusRepository->method('getByProfile')->willReturn([
			self::getStatusEntity(),
		]);
		$this->statusRepository->method('getNextCursor')->willReturn('test_cursor');
		$this->statusService->method('getCount')->willReturn(1);

		// Wall surface uses the 3-gate safety+platform filter, never the
		// 5-gate feed filter.
		$this->wallVisibilityService->expects($this->once())
			->method('filterStatusesForWall')
			->willReturnArgument(0);
		$this->wallVisibilityService->expects($this->never())
			->method('filterStatusesForFeed');
		$this->wallVisibilityService->expects($this->once())
			->method('filterVisibleComments')
			->willReturnArgument(0);

		$result = $this->statusService->getByProfile($wallId);

		$this->assertEquals($expected, $result);
	}

	public static function getByProfileProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'wallId' => 1,
				'expected' => [
					'data' => [self::getStatusEntity()],
					'permissions' => [
						'delete' => true,
						'edit' => false,
						'post' => true,
						'postComments' => true,
					],
					'pagination' => [
						'nextCursor' => null,
						'hasMore' => false,
					],
					'total' => 1,
				],
			],
		];
	}

	protected static function getStatusEntity(): StatusEntity
	{
		return StatusEntity::from([
			'id' => 1,
			'wall_id' => 1,
			'user_id' => 1,
			'body' => 'test status',
		]);
	}

	public function testRecountComments(): void
	{
		$this->statusRepository = $this->createMock(StatusRepositoryInterface::class);
		$this->statusService = new StatusService(
			$this->statusRepository,
			$this->userRepository,
			$this->permissionsService,
			$this->wallVisibilityService,
			null
		);

		$this->statusRepository->expects($this->once())
			->method('recountComments');

		$this->statusService->recountComments();
	}

	public function testRecountLikes(): void
	{
		$this->statusRepository = $this->createMock(StatusRepositoryInterface::class);
		$this->statusService = new StatusService(
			$this->statusRepository,
			$this->userRepository,
			$this->permissionsService,
			$this->wallVisibilityService,
			null
		);

		$this->statusRepository->expects($this->once())
			->method('recountLikes');

		$this->statusService->recountLikes();
	}

	/**
	 * @throws EmptyDataException
	 * @throws DataNotFoundException
	 */
	public function testGetById(): void
	{
		$statusId = 123;
		$wallId = 1;
		$currentUserId = 1;
		$statusEntity = StatusEntity::from([
			'id' => $statusId,
			'wall_id' => $wallId,
			'user_id' => $currentUserId,
			'body' => 'Test single status',
		]);

		$expectedPermissions = [
			'delete' => true,
			'edit' => false,
			'post' => true,
			'postComments' => true,
		];

		$expected = [
			'data' => [$statusEntity],
			'permissions' => $expectedPermissions,
			'pagination' => [
				'nextCursor' => null,
				'hasMore' => false,
			],
			'total' => 1,
		];

		$this->statusRepository->method('getById')->willReturn($statusEntity);

		$this->permissionsService = $this->createMock(PermissionsServiceInterface::class);
		$this->permissionsService->method('permissions')
			->with($wallId, $currentUserId)
			->willReturn($expectedPermissions);

		// Single-status surface uses the 3-gate safety+platform filter.
		$this->wallVisibilityService->expects($this->once())
			->method('filterStatusesForWall')
			->willReturnArgument(0);
		$this->wallVisibilityService->expects($this->once())
			->method('filterVisibleComments')
			->willReturnArgument(0);

		$this->statusService = $this->getMockBuilder(StatusService::class)
			->setConstructorArgs([
				$this->statusRepository,
				$this->userRepository,
				$this->permissionsService,
				$this->wallVisibilityService,
				null,
			])
			->onlyMethods(['currentUserInfo'])
			->getMock();

		$this->statusService->method('currentUserInfo')->willReturn(['id' => $currentUserId]);

		$result = $this->statusService->getById($statusId);

		$this->assertEquals($expected, $result);
	}

	/**
	 * When the visibility filter hides the requested status, the service
	 * raises DataNotFoundException so the controller surfaces a 404 rather
	 * than leaking that the row exists.
	 */
	public function testGetByIdThrowsWhenStatusIsFilteredOut(): void
	{
		$statusId = 123;
		$statusEntity = StatusEntity::from([
			'id' => $statusId,
			'wall_id' => 1,
			'user_id' => 2,
			'body' => 'blocked content',
		]);

		$this->statusRepository->method('getById')->willReturn($statusEntity);

		$this->wallVisibilityService->expects($this->once())
			->method('filterStatusesForWall')
			->willReturn([]);
		$this->wallVisibilityService->expects($this->never())
			->method('filterVisibleComments');

		$this->statusService = $this->getMockBuilder(StatusService::class)
			->setConstructorArgs([
				$this->statusRepository,
				$this->userRepository,
				$this->permissionsService,
				$this->wallVisibilityService,
				null,
			])
			->onlyMethods(['currentUserInfo'])
			->getMock();
		$this->statusService->method('currentUserInfo')->willReturn(['id' => 1]);

		$this->expectException(DataNotFoundException::class);
		$this->expectExceptionMessage('error_no_status');

		$this->statusService->getById($statusId);
	}

	/**
	 * @throws EmptyDataException
	 */
	public function testGetByBuddiesReturnsEmptyArrayWhenViewerHasNoBuddies(): void
	{
		$this->userRepository->method('getById')->willReturn(
			UserSettingsEntity::from(['buddies' => '', 'paginationNumber' => 5])
		);

		$this->statusRepository = $this->createMock(StatusRepositoryInterface::class);
		// Even with no buddies the viewer's own ID is always included, so
		// getByBuddyActivity must be called with just [viewerId].
		$this->statusRepository->expects($this->once())
			->method('getByBuddyActivity')
			->with([1], 5, null, [], 1)
			->willReturn([]);
		$this->wallVisibilityService->expects($this->once())
			->method('getMutualBlockIds')
			->with(1, [])
			->willReturn([]);
		$this->wallVisibilityService->expects($this->once())
			->method('filterStatusesForFeed')
			->willReturn([]);

		$this->statusService = $this->getMockBuilder(StatusService::class)
			->setConstructorArgs([
				$this->statusRepository,
				$this->userRepository,
				$this->permissionsService,
				$this->wallVisibilityService,
				null,
			])
			->onlyMethods(['currentUserInfo'])
			->getMock();
		$this->statusService->method('currentUserInfo')->willReturn(['id' => 1]);

		$result = $this->statusService->getByBuddies();
		$this->assertSame([], $result['data']);
	}

	/**
	 * @throws EmptyDataException
	 */
	public function testGetByBuddiesQueriesByBuddyActivityAndAppliesFeedFilter(): void
	{
		$this->userRepository->method('getById')->willReturn(
			UserSettingsEntity::from(['buddies' => '2,3', 'paginationNumber' => 5])
		);
		$this->permissionsService = $this->createMock(PermissionsServiceInterface::class);
		// On the general wall both arguments must be the viewer's own ID so that
		// PermissionsService does not short-circuit on a zero profileOwner.
		$this->permissionsService->expects($this->once())
			->method('permissions')
			->with(1, 1)
			->willReturn([
				'delete' => true, 'edit' => false, 'post' => true, 'postComments' => true,
			]);
		$this->statusRepository = $this->createMock(StatusRepositoryInterface::class);
		$this->statusRepository->expects($this->once())
			->method('getByBuddyActivity')
			->with([1, 2, 3], 5, null, [], 1)
			->willReturn([self::getStatusEntity()]);
		$this->statusRepository->method('getNextCursor')->willReturn('test_cursor');

		// Feed surface uses the 5-gate filter, never the 3-gate wall filter.
		$this->wallVisibilityService->expects($this->once())
			->method('getMutualBlockIds')
			->with(1, [2, 3])
			->willReturn([]);
		$this->wallVisibilityService->expects($this->once())
			->method('filterStatusesForFeed')
			->willReturnArgument(0);
		$this->wallVisibilityService->expects($this->never())
			->method('filterStatusesForWall');
		$this->wallVisibilityService->expects($this->once())
			->method('filterVisibleComments')
			->willReturnArgument(0);

		$this->statusService = $this->getMockBuilder(StatusService::class)
			->setConstructorArgs([
				$this->statusRepository,
				$this->userRepository,
				$this->permissionsService,
				$this->wallVisibilityService,
				null,
			])
			->onlyMethods(['currentUserInfo', 'getCount'])
			->getMock();
		$this->statusService->method('currentUserInfo')->willReturn(['id' => 1]);
		$this->statusService->method('getCount')->willReturn(1);

		$result = $this->statusService->getByBuddies();

		$this->assertSame(self::getStatusEntity()->getId(), $result['data'][0]->getId());
		$this->assertSame(1, $result['total']);
	}
}
