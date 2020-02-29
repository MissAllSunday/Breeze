<?php

declare(strict_types=1);

use Breeze\Service\Request as RequestService;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
	/**
	 * @var RequestService
	 */
	private $requestService;

	protected function setUp(): void
	{
		$this->requestService = new RequestService();
	}

	/**
	 * @dataProvider getProvider
	 */
	public function testGet(string  $variableName, $expected): void
	{
		$requestVariable = $this->requestService->get($variableName);

		$this->assertEquals($expected, $requestVariable);
	}

	public function getProvider(): array
	{
		return [
		    'sanitized' =>
		    [
		        'variableName' => 'xss',
		        'expected' => '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;'
		    ],
		    'not found' =>
		    [
		        'variableName' => 'Cornholio',
		        'expected' => false
		    ]
		];
	}

	/**
	 * @dataProvider sanitizeProvider
	 */
	public function testSanitize(string  $variableName, $expected): void
	{
		$requestVariable = $this->requestService->sanitize($variableName);

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
