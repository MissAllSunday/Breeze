<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Repository\User\UserRepositoryInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StatusServiceTest extends TestCase
{

	private StatusRepositoryInterface | MockObject $statusRepository;
	private UserRepositoryInterface | MockObject $userRepository;
	private PermissionsServiceInterface | MockObject $permissionsService;
	private StatusService $statusService;

	/**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		$this->statusRepository = $this->createMock(StatusRepositoryInterface::class);
		$this->userRepository = $this->createMock(UserRepositoryInterface::class);
		$this->permissionsService = $this->createMock(PermissionsServiceInterface::class);

		$this->statusService = new StatusService($this->statusRepository, $this->userRepository, $this->permissionsService);
	}

	#[DataProvider('getWallUserSettingsProvider')]
	public function testGetWallUserSettings(int $wallId, string $valueName, array $userSettings, mixed $expected): void
	{
		$this->userRepository->method('getById')->willReturn($userSettings);

		$result =$this->statusService->getWallUserSettings($wallId, $valueName);

		$this->assertEquals($expected, $result);
	}

	public static function getWallUserSettingsProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'wallId' => 1,
				'valueName' => 'paginationNumber',
				'userSettings' => ['paginationNumber' => 1],
				'expected' => 1,
			],
			'return all values' => [
				'wallId' => 1,
				'valueName' => '',
				'userSettings' => ['paginationNumber' => 1],
				'expected' => ['paginationNumber' => 1],
			],
		];
	}
	#[DataProvider('getByProfileProvider')]
	public function testGetByProfile(int $wallId, int $start, array $expected): void
	{
		$this->userRepository->method('getById')->willReturn(['paginationNumber' => 5]);
		$this->permissionsService->method('permissions')->willReturn([
			'delete' => true,
			'edit' => false,
			'post' => true,
			'postComments' => true,
		]);
		$this->statusRepository->method('getByProfile')->willReturn([
			'data' => [], 'total' => 1
		]);

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
					'data' => [], 'total' => 1,
					'permissions' => [
						'delete' => true,
						'edit' => false,
						'post' => true,
						'postComments' => true,
					]
				],
			],
		];
	}
}
