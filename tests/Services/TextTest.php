<?php

declare(strict_types=1);

use Breeze\Service\Text as TextService;
use PHPUnit\Framework\TestCase;

/**
 * @covers TextService
 */
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
	 * @dataProvider getSmfProvider
	 */
	public function testGetSmf(string $textKeyName, string $expected): void
	{
		$text = $this->textService->getSmf($textKeyName);

		$this->assertEquals($expected, $text);
	}

	public function getSmfProvider(): array
	{
		return [
		    'text exists' =>
				[
				    'textKeyName' => 'time_year',
				    'expected' => 'year'
				],
		    'text doesnt exists' =>
				[
				    'textKeyName' => 'nope!',
				    'expected' => ''
				]
		];
	}

	/**
	 * @dataProvider getProvider
	 */
	public function testGet(string $textKeyName, string $expected): void
	{
		$text = $this->textService->get('lol');

		$this->assertEquals('lol', $text);
	}

	public function getProvider(): array
	{
		return [
		    'text exists' =>
		    	[
		    	    'textKeyName' => 'lol',
		    	    'expected' => 'lol'
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
		$normal = $this->textService->normalizeString("\xC3\x85 á é í ó ú");

		$this->assertEquals('A a e i o u', $normal);
	}

	/**
	 * @dataProvider formatBytesProvider
	 */
	public function testformatBytes(int $bytes, bool $showUnit, string  $expected): void
	{
		$formattedBytes = $this->textService->formatBytes($bytes, $showUnit);

		$this->assertEquals($expected, $formattedBytes);

	}

	public function formatBytesProvider(): array
	{
		return [
		    'happy path' =>
				[
				    'bytes' => 13516800,
				    'showUnit' => true,
				    'expected' => '12.8906 MB'
				],
		    'no unit' =>
		    	[
		    	    'bytes' => 666666666666,
		    	    'showUnits' => false,
		    	    'expected' =>'620.8817'
		    	],
		];
	}

	/**
	 * @dataProvider truncateProvider
	 */
	public function testTruncate(string $stringToTruncate, int $limit, string $break, string $pad, string $expected): void
	{
		$truncatedString = $this->textService->truncate($stringToTruncate, $limit, $break, $pad);

		$this->assertEquals($expected, $truncatedString);
	}

	public function truncateProvider(): array
	{
		return [
		    'happy path' =>
				[
				    'stringToTruncate' => 'Contritium praecedit superbia',
				    'limit' => 8,
				    'break' => ' ',
				    'pad' => '...',
				    'expected' => 'Contritium...'
				],
		    'smaller than limit' =>
		    	[
		    	    'stringToTruncate' => 'Fidite Nemini',
		    	    'limit' => 666,
		    	    'break' => '',
		    	    'pad' => '',
		    	    'expected' => 'Fidite Nemini'
		    	],
		    'different pad' =>
		    	[
		    	    'stringToTruncate' => 'Mendacem memorem esse oportet',
		    	    'limit' => 12,
		    	    'break' => ' ',
		    	    'pad' => '---',
		    	    'expected' => 'Mendacem memorem---'
		    	],
		    'no string' =>
		    	[
		    	    'stringToTruncate' => '',
		    	    'limit' => 0,
		    	    'break' => '',
		    	    'pad' => '',
		    	    'expected' => ''
		    	],
		];
	}

	/**
	 * @dataProvider timeElapsedProvider
	 */
	public function testTimeElapsed(int $timeInSeconds, string $expected): void
	{
		$timeAgo = $this->textService->timeElapsed($timeInSeconds);

		$this->assertEquals($expected, $timeAgo);
	}

	public function timeElapsedProvider(): array
	{
		return [
		    'just now' =>
		    	[
		    	    'timeInSeconds' => time(),
		    	    'expected' => 'just now'
		    	],
		    'years ago' =>
				[
				    'timeInSeconds' => time() - 60489000,
				    'expected' => '2 years ago'
				],
		    'hours' =>
				[
				    'timeInSeconds' => time() - 82800,
				    'expected' => '23 hours ago'
				],
		    'minute' =>
				[
				    'timeInSeconds' => time() - 62,
				    'expected' => '1 minute ago'
				],
		];
	}
}
