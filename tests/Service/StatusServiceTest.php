<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\StatusHandledEntity;
use Breeze\Entity\UserSettingsHandledEntity;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Repository\User\SettingsRepositoryInterface;
use Breeze\Util\Validate\EmptyDataException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
		$this->statusRepository = $this->createMock(StatusRepositoryInterface::class);
		$this->userRepository = $this->createMock(SettingsRepositoryInterface::class);
		$this->permissionsService = $this->createMock(PermissionsServiceInterface::class);
		$this->statusService = $this->getMockBuilder(StatusService::class)
			->setConstructorArgs([$this->statusRepository,
				$this->userRepository,
				$this->permissionsService])
			->onlyMethods(['getCount'])
			->getMock();
	}

	/**
	 * @throws EmptyDataException
	 */
	#[DataProvider('getByProfileProvider')]
	public function testGetByProfile(int $wallId, int $start, array $expected): void
	{
		$this->userRepository->method('getById')->willReturn(new UserSettingsHandledEntity(['paginationNumber' => 5]));
		$this->permissionsService->method('permissions')->willReturn([
			'delete' => true,
			'edit' => false,
			'post' => true,
			'postComments' => true,
		]);
		$this->statusRepository->method('getByProfile')->willReturn([
			self::getStatusHandledEntity(),
		]);
		$this->statusService->method('getCount')->willReturn(1);

		$result = $this->statusService->getByProfile($wallId, $start);

		$this->assertEquals($expected, $result);
	}

	public static function getByProfileProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'wallId' => 1,
				'start' => 1,
				'expected' => [
					'data' => [self::getStatusHandledEntity()], 'total' => 1,
					'permissions' => [
						'delete' => true,
						'edit' => false,
						'post' => true,
						'postComments' => true,
					],
				],
			],
		];
	}

	protected static function getStatusHandledEntity(): StatusHandledEntity
	{
		return new StatusHandledEntity([
			'id' => 1,
			'wallId' => 1,
			'userId' => 1,
			'body' => 'test status',
		]);
	}
}
