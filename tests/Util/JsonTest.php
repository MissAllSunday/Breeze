<?php

declare(strict_types=1);


namespace Breeze\Util;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class JsonTest  extends TestCase
{
	#[DataProvider('isJsonProvider')]
	public function testIsJson($json, $expected): void
	{
		$isJson = Json::isJson($json);

		$this->assertEquals( $expected, $isJson);
	}

	public static function isJsonProvider(): array
	{
		return [
			'is json' =>
				[
					'json' => json_encode(['Ace', 'Luffy', 'Sabo']),
					'expected' => true,
				],
			'is not json' =>
				[
					'json' => 'Im Jason!',
					'expected' => false,
				],
			'empty json' =>
				[
					'json' => Json::encode([]),
					'expected' => true,
				],
		];
	}
}
