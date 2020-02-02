<?php

declare(strict_types=1);

use Breeze\Service\Text as TextService;
use PHPUnit\Framework\TestCase;

final class TextTest extends TestCase
{
	/**
	 * @var TextService
	 */
	private $textService;

	protected function setUp(): void
	{
		$this->textService = new TextService();
	}

	/**
	 * @dataProvider getProvider
	 */
	public function testGet(string $textKeyName, string $expected): void
	{
		$text = $this->textService->get($textKeyName);

		$this->assertEquals($expected, $text);
	}

	public function getProvider(): array
	{
		return [
		    'text exists' =>
		    [
		        'textKeyName' => 'time_year',
		        'expected' => 'years'
		    ],
		    'text doesnt exists' =>
		    [
		        'textKeyName' => 'nope!',
		        'expected' => ''
		    ]
		];
	}

	/**
	 * @dataProvider parserProvider
	 */
	public function testParser(): void
	{

	}

	public function parserProvider(): array
	{
		return [
		    'text exists' =>
		    [
		        'textKeyName' => 'time_year',
		        'expected' => 'years'
		    ],
		    'text doesnt exists' =>
		    [
		        'textKeyName' => 'nope!',
		        'expected' => ''
		    ]
		];
	}
}
