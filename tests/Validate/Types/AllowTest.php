<?php

declare(strict_types=1);

namespace Breeze\Validate\Types;

use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class AllowTest extends TestCase
{
	private Allow $allow;

	public function setUp(): void
	{
		$this->allow = new Allow();
	}

	/**
	 * @throws NotAllowedException
	 */
	#[DataProvider('permissionsProvider')]
	public function testPermissions(string $permissionName, string $permissionMessageKey, bool $isExpectedException): void
	{
		if ($isExpectedException) {
			$this->expectException(NotAllowedException::class);
			$this->expectExceptionMessage($permissionMessageKey);
		} else {
			$this->expectNotToPerformAssertions();
		}

		$this->allow->permissions($permissionName, $permissionMessageKey);
	}

	public static function permissionsProvider(): array
	{
		return [
			'allowed' => [
				'permissionName' => 'yep',
				'permissionMessageKey' => 'yep',
				'isExpectedException' => false,
			],
			'not allowed' => [
				'permissionName' => 'nope',
				'permissionMessageKey' => 'nope',
				'isExpectedException' => true,
			],
		];
	}

	#[DataProvider('floodControlProvider')]
	public function testFloodControl(int $posterId, bool $isExpectedException): void
	{
		if ($isExpectedException) {
			$this->expectException(NotAllowedException::class);
			$this->expectExceptionMessage('flood');
		} else {
			$this->expectNotToPerformAssertions();
		}

		$this->allow->floodControl($posterId);
	}

	public static function floodControlProvider(): array
	{
		return [
			'happy path' =>
				[
					'posterId' => 1,
					'isExpectedException' => false,
				],
			'flood trigger' =>
				[
					'posterId' => 2,
					'isExpectedException' => true,
				],
			'flood data is empty' =>
				[
					'posterId' => 5,
					'isExpectedException' => false,
				],
			'unset persistence value' =>
				[
					'posterId' => 4,
					'isExpectedException' => false,
				],
		];
	}

	/**
	 * @throws DataNotFoundException
	 */
	#[TestWith(['CompressedOutput', true])]
	#[TestWith(['Breeze_master', false])]
	public function testIsFeatureEnable(string $featureName, bool $isExpectedException): void
	{
		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
			$this->expectExceptionMessage('');
		} else {
			$this->expectNotToPerformAssertions();
		}

		$this->allow->isFeatureEnable($featureName);
	}
}
