<?php

declare(strict_types=1);

namespace Breeze\Traits;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PersistenceTest extends TestCase
{
	/**
	 */
	private MockObject|PersistenceTrait $persistenceTrait;

	protected function setUp(): void
	{
		$this->persistenceTrait = $this->getMockForTrait(PersistenceTrait::class);
	}

	public function testGetMessage(): void
	{
		$message = $this->persistenceTrait->getPersistenceMessage();

		$this->assertIsArray($message);
		$this->assertEquals([
			'message' => 'Kaizoku ou ni ore wa naru',
			'type' => 'info',
		], $message);
	}

	/**
	 * @dataProvider setMessageProvider
	 */
	public function testSetMessage($message, $type, $expectedResult): void
	{
		if ($type !== null) {
			$setMessage = $this->persistenceTrait->setPersistenceMessage($message, $type);
		} else {
			$setMessage = $this->persistenceTrait->setPersistenceMessage($message);
		}

		$this->assertEquals($expectedResult, $setMessage);
	}

	public function setMessageProvider(): array
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
