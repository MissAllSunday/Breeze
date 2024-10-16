<?php

declare(strict_types=1);

namespace Breeze\Traits;

use Breeze\Traits\RequestTrait as RequestTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
	use RequestTrait;

	#[DataProvider('getRequestProvider')]
	public function testGet(string  $variableName, $expected, ?string $defaultValue): void
	{
		$requestVariable = $this->getRequest($variableName, $defaultValue);

		$this->assertEquals($expected, $requestVariable);
	}

	public static function getRequestProvider(): array
	{
		return [
			'sanitized' =>
				[
					'variableName' => '<script>alert("XSS");</script>',
					'expected' => 'Luffy',
					'defaultValue' => 'Luffy',
				],
			'not found' =>
			[
				'variableName' => 'Cornholio',
				'expected' => false,
				'defaultValue' => null,
			],
			'default value' =>
				[
					'variableName' => 'Cornholio',
					'expected' => 'Luffy',
					'defaultValue' => 'Luffy',
				],
		];
	}

	#[DataProvider('sanitizeProvider')]
	public function testSanitize(string  $variableName, $expected): void
	{
		$requestVariable = $this->sanitize($variableName);

		$this->assertEquals($expected, $requestVariable);
	}

	public static function sanitizeProvider(): array
	{
		return [
			'sanitized' =>
			[
				'variableName' => '<script>alert("XSS");</script>',
				'expected' => '&lt;script&gt;alert(&quot;XSS&quot;);&lt;/script&gt;',
			],
			'int' =>
			[
				'variableName' => '666',
				'expected' => 666,
			],
			'empty' =>
			[
				'variableName' => '0',
				'expected' => false,
			],
		];
	}
}
