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
	public function testParser(string $textToParse, array $replacements, string  $expected): void
	{
		$parsedText = $this->textService->parser($textToParse, $replacements);

		$this->assertEquals($expected, $parsedText);
	}

	public function parserProvider(): array
	{
		return [
		    'empty text' =>
		    [
		        'textToParse' => '',
		        'replacements' => [],
		        'expected' => ''
		    ],
		    'no replacement' =>
		    	[
		    	    'textToParse' => 'Non nobis solum',
		    	    'replacements' => [],
		    	    'expected' => 'Non nobis solum'
		    	],
		    'normal text' =>
				[
				    'textToParse' => 'Hello {general_kenobi}',
				    'replacements' => ['general_kenobi' => 'there!'],
				    'expected' => 'Hello there!'
				],
		    'href text' =>
		    	[
		    	    'textToParse' => '<a href="{href}">{zim}</a>',
		    	    'replacements' => [
		    	        'href' => 'https://www.youtube.com/watch?v=waEC-8GFTP4',
		    	        'zim' => 'Ain\'t Nobody Got Time For That'
		    	    ],
		    	    'expected' => '<a href="https://www.youtube.com/watch?v=waEC-8GFTP4;foo=baz">Ain\'t Nobody Got Time For That</a>'
		    	],
		];
	}

	/**
	 * @dataProvider commaSeparatedProvider
	 */
	public function testCommaSeparated(string  $string, string  $type, string  $expected): void
	{
		$commaSeparatedString = $this->textService->commaSeparated($string, $type);

		$this->assertEquals($expected, $commaSeparatedString);
	}

	public function commaSeparatedProvider(): array
	{
		$dirtyString = 'QWE,"#$5,#$%V,B$%B&3,666';

		return [
		    'alphanumeric' =>
		    [
		        'string' => $dirtyString,
		        'type' => 'alphanumeric',
		        'expected' => 'QWE,5,V,BB3,666'
		    ],
		    'alpha' =>
		    	[
		    	    'string' => $dirtyString,
		    	    'type' => 'alpha',
		    	    'expected' => 'QWE,V,BB'
		    	],
		    'numeric' =>
		    	[
		    	    'string' => $dirtyString,
		    	    'type' => 'numeric',
		    	    'expected' => '5,3,666'
		    	],
		    'empty' =>
		    	[
		    	    'string' => '',
		    	    'type' => '',
		    	    'expected' => ''
		    	],
		    'no type' =>
		    	[
		    	    'string' => $dirtyString,
		    	    'type' => '',
		    	    'expected' => 'QWE,5,V,BB3,666'
		    	],
		];
	}

	public function testNormalizeString(): void
	{
		$normal = $this->textService->normalizeString("\xC3\x85");

		$this->assertEquals('A', $normal);
	}
}
