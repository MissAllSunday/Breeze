<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\StatusEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Repository\User\SettingsRepositoryInterface;
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

	private MockObject|StatusService $statusService;

	/**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		$this->statusRepository = $this->createStub(StatusRepositoryInterface::class);
		$this->userRepository = $this->createStub(SettingsRepositoryInterface::class);
		$this->permissionsService = $this->createStub(PermissionsServiceInterface::class);
		$this->statusService = $this->getMockBuilder(StatusService::class)
			->setConstructorArgs([$this->statusRepository,
				$this->userRepository,
				$this->permissionsService,
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
			null
		);

		$this->statusRepository->expects($this->once())
			->method('recountLikes');

		$this->statusService->recountLikes();
	}

	/**
	 * @throws EmptyDataException
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

		// Mock the repository to return the status entity
		$this->statusRepository->method('getById')->willReturn($statusEntity);

		// Mock permissions service
		$this->permissionsService->method('permissions')
			->with($wallId, $currentUserId)
			->willReturn($expectedPermissions);

		// Mock currentUserInfo
		$this->statusService = $this->getMockBuilder(StatusService::class)
			->setConstructorArgs([
				$this->statusRepository,
				$this->userRepository,
				$this->permissionsService,
				null,
			])
			->onlyMethods(['currentUserInfo'])
			->getMock();

		$this->statusService->method('currentUserInfo')->willReturn(['id' => $currentUserId]);

		$result = $this->statusService->getById($statusId);

		$this->assertEquals($expected, $result);
	}
}
