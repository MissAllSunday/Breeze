<?php

declare(strict_types=1);

namespace Breeze\Traits;

use Breeze\Traits\RequestTrait as RequestTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
	/**
	 * @var RequestTrait&MockObject
	 */
	private $requestTrait;

	protected function setUp(): void
	{
		$this->requestTrait = $this->getMockForTrait(RequestTrait::class);
	}

	/**
	 * @dataProvider getRequestProvider
	 */
	public function testGet(string  $variableName, $expected, ?string $defaultValue): void
	{
		$requestVariable = $this->requestTrait->getRequest($variableName, $defaultValue);

		$this->assertEquals($expected, $requestVariable);
	}

	public function getRequestProvider(): array
	{
		return [
			'sanitized' =>
			[
				'variableName' => 'xss',
				'expected' => '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;',
				'defaultValue' => null,
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
				]
		];
	}

	/**
	 * @dataProvider sanitizeProvider
	 */
	public function testSanitize(string  $variableName, $expected): void
	{
		$requestVariable = $this->requestTrait->sanitize($variableName);

		$this->assertEquals($expected, $requestVariable);
	}

	public function sanitizeProvider(): array
	{
		return [
			'sanitized' =>
			[
				'dirty' => '<script>alert("XSS");</script>',
				'expected' => '&lt;script&gt;alert(&quot;XSS&quot;);&lt;/script&gt;'
			],
			'int' =>
			[
				'variable' => '666',
				'expected' => 666
			],
			'empty' =>
			[
				'variable' => '0',
				'expected' => false
			],
		];
	}
}
