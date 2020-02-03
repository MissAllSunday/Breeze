<?php

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
}