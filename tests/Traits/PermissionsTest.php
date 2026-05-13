<?php

declare(strict_types=1);

namespace Breeze\Traits;

use Breeze\Enums\PermissionsEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PermissionsTest extends TestCase
{
	use PermissionsTrait;

	protected function setUp(): void
	{
		parent::setUp();
		$GLOBALS['user_info']['is_guest'] = false;
	}

	public function testIsNotGuestDoesNotThrowWhenUserIsNotGuest(): void
	{
		$this->expectNotToPerformAssertions();
		$this->isNotGuest('error_no_access');
	}

	public function testIsNotGuestThrowsWhenUserIsGuest(): void
	{
		$GLOBALS['user_info']['is_guest'] = true;

		$this->expectException(\Error::class);
		$this->expectExceptionMessage('error_no_access');

		$this->isNotGuest('error_no_access');
	}

	#[DataProvider('isAllowedToProvider')]
	public function testIsAllowedTo(string $permissionName, bool $expected): void
	{
		$this->assertEquals($expected, $this->isAllowedTo($permissionName));
	}

	public static function isAllowedToProvider(): array
	{
		return [
			'breeze permission true' => [
				'permissionName' => 'yep',
				'expected' => true,
			],
			'breeze permission false' => [
				'permissionName' => 'nope',
				'expected' => false,
			],
			'smf permission likes_like' => [
				'permissionName' => PermissionsEnum::LIKES_LIKE,
				'expected' => false,
			],
			'smf permission admin_forum' => [
				'permissionName' => PermissionsEnum::ADMIN_FORUM,
				'expected' => false,
			],
			'unknown permission' => [
				'permissionName' => 'unknownPermission',
				'expected' => false,
			],
		];
	}
}
