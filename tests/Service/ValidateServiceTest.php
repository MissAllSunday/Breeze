<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Traits\PermissionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidateServiceTest extends TestCase
{
	private ValidateService $validateService;

	public function setUp(): void
	{
		$this->validateService = new ValidateService();
	}

	#[DataProvider('permissionsProvider')]
	public function testPermissions(string $type, int $profileOwner, int $userPoster, array $expected): void
	{
		$actual = $this->validateService->permissions($type, $profileOwner, $userPoster);

		$this->assertEquals($expected, $actual);
	}

	public static function permissionsProvider(): array
	{
		return [
			'do nothing' => [
				'type' => '',
				'profileOwner' => 0,
				'userPoster' => 0,
				'expected' => [
					'edit' => false,
					'delete' => '',
					'post' => false,
					'postComments' => false,
				],
			],
			'isProfileOwner' => [
				'type' => 'Status',
				'profileOwner' => 666,
				'userPoster' => 1,
				'expected' => [
					'edit' => false,
					'delete' => true,
					'post' => true,
					'postComments' => true,
				],
			],
		];
	}
}
