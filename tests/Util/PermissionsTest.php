<?php

declare(strict_types=1);

namespace Breeze\Util;

use Breeze\Service\ValidateService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PermissionsTest extends TestCase
{
	private Permissions $permissions;

	public function setUp(): void
	{
		$this->permissions = new Permissions();
	}

	#[DataProvider('isAllowedToProvider')]
	public function testIsAllowedTo(string $permissionName, bool $expectedResult): void
	{
		$isAllowedTo = $this->permissions->isAllowedTo($permissionName);

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
