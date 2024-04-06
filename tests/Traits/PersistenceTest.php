<?php

declare(strict_types=1);

namespace Breeze\Traits;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PersistenceTest extends TestCase
{
	use PersistenceTrait;

	public function testGetMessage(): void
	{
		$message = $this->getPersistenceMessage();

		$this->assertIsArray($message);
		$this->assertEquals([
			'message' => 'Kaizoku ou ni ore wa naru',
			'type' => 'info',
		], $message);
	}

	#[DataProvider('setMessageProvider')]
	public function testSetMessage($message, $type, $expectedResult): void
	{
		if ($type !== null) {
			$setMessage = $this->setPersistenceMessage($message, $type);
		} else {
			$setMessage = $this->setPersistenceMessage($message);
		}

		$this->assertEquals($expectedResult, $setMessage);
	}

	public static function setMessageProvider(): array
	{
		return [
			'empty params' => [
				'message' => '',
				'type' => '',
				'expectedResult' => [],
			],
			'no type' => [
				'message' => 'One Piece',
				'type' => null,
				'expectedResult' => [
					'message' => 'One Piece',
					'type' => 'info',
				],
			],
			'happy path' => [
				'message' => 'One Piece',
				'type' => 'error',
				'expectedResult' => [
					'message' => 'One Piece',
					'type' => 'error',
				],
			],
		];
	}
}
