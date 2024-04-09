<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Traits\PermissionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
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
}
