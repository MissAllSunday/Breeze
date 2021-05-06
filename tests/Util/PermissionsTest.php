<?php

declare(strict_types=1);

namespace Breeze\Util;

use PHPUnit\Framework\TestCase;

class PermissionsTest extends TestCase
{
	/**
	 * @dataProvider isAllowedToProvider
	 */
	public function testIsAllowedTo(string $permissionName, bool $expectedResult): void
	{
		$isAllowedTo = Permissions::isAllowedTo($permissionName);

		$this->assertEquals($expectedResult, $isAllowedTo);
	}

	public function isAllowedToProvider(): array
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
