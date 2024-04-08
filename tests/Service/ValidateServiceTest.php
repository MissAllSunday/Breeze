<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Util\Permissions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ValidateServiceTest extends TestCase
{
	private ValidateService $validateService;

	private Permissions&MockObject $permissions;

	/**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		$this->permissions = $this->createMock(Permissions::class);
		$this->validateService = new ValidateService($this->permissions);
	}

	#[DataProvider('permissionsProvider')]
	public function testPermissions(string $type, int $profileOwner, int $userPoster, array $expected): void
	{
		$actual = $this->validateService->permissions($type, $profileOwner, $userPoster);

		$this->permissions->method('isAllowedTo')->willReturn(true);

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
					'delete' => '',
					'post' => true,
					'postComments' => true,
				],
			],
		];
	}
}
