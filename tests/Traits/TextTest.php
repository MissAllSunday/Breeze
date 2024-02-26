<?php

declare(strict_types=1);

namespace Breeze\Traits;

use Breeze\Traits\TextTrait as TextTrait;
use PHPUnit\Framework\TestCase;

final class TextTest extends TestCase
{
	use TextTrait;

	/**
	 * @dataProvider getSmfProvider
	 */
	public function testGetSmf(string $textKeyName, string $expected): void
	{
		$text = $this->getSmfText($textKeyName);

		$this->assertEquals($expected, $text);
	}

	public static function getSmfProvider(): array
	{
		return [
			'text exists' =>
			[
				'textKeyName' => 'time_year',
				'expected' => 'year',
			],
			'text doesnt exists' =>
			[
				'textKeyName' => 'nope!',
				'expected' => '',
			],
		];
	}

	/**
	 * @dataProvider getTextProvider
	 */
	public function testGetText(string $textKeyName, string $expected): void
	{
		$text = $this->getText($textKeyName);

		$this->assertEquals($expected, $text);
	}

	public static function getTextProvider(): array
	{
		return [
			'text exists' =>
			[
				'textKeyName' => 'lol',
				'expected' => 'lol',
			],
			'text doesnt exists' =>
			[
				'textKeyName' => 'nope!',
				'expected' => '',
			],
		];
	}

	/**
	 * @dataProvider parserTextProvider
	 */
	public function testParserText(string $textToParse, array $replacements, string  $expected): void
	{
		$parsedText = $this->parserText($textToParse, $replacements);

		$this->assertEquals($expected, $parsedText);
	}

	public static function parserTextProvider(): array
	{
		return [
			'empty text' =>
			[
				'textToParse' => '',
				'replacements' => [],
				'expected' => '',
			],
			'no replacement' =>
			[
				'textToParse' => 'Non nobis solum',
				'replacements' => [],
				'expected' => 'Non nobis solum',
			],
			'normal text' =>
			[
				'textToParse' => 'Hello {general_kenobi}',
				'replacements' => ['general_kenobi' => 'there!'],
				'expected' => 'Hello there!',
			],
			'href text' =>
			[
				'textToParse' => '
				<a href="{href}">{zim}</a>',
				'replacements' => [
					'href' => 'https://www.youtube.com/watch?v=waEC-8GFTP4',
					'zim' => 'Ain\'t Nobody Got Time For That',
				],
				'expected' => '
				<a href="https://www.youtube.com/watch?v=waEC-8GFTP4;foo=baz">Ain\'t Nobody Got Time For That</a>',
			],
		];
	}

	/**
	 * @dataProvider commaSeparatedProvider
	 */
	public function testCommaSeparated(string  $string, string  $type, string  $expected): void
	{
		$commaSeparatedString = $this->commaSeparated($string, $type);

		$this->assertEquals($expected, $commaSeparatedString);
	}

	public static function commaSeparatedProvider(): array
	{
		$dirtyString = 'QWE,"#$5,#$%V,B$%B&3,666';

		return [
			'alphanumeric' =>
			[
				'string' => $dirtyString,
				'type' => 'alphanumeric',
				'expected' => 'QWE,5,V,BB3,666',
			],
			'alpha' =>
			[
				'string' => $dirtyString,
				'type' => 'alpha',
				'expected' => 'QWE,V,BB',
			],
			'numeric' =>
			[
				'string' => $dirtyString,
				'type' => 'numeric',
				'expected' => '5,3,666',
			],
			'empty' =>
			[
				'string' => '',
				'type' => '',
				'expected' => '',
			],
			'no type' =>
			[
				'string' => $dirtyString,
				'type' => '',
				'expected' => 'QWE,5,V,BB3,666',
			],
		];
	}

	public function testNormalizeString(): void
	{
		$normal = $this->normalizeString("\xC3\x85 á é í ó ú");

		$this->assertEquals('A a e i o u', $normal);
	}

	/**
	 * @dataProvider formatBytesProvider
	 */
	public function testFormatBytes(int $bytes, bool $showUnit, string  $expected): void
	{
		$formattedBytes = $this->formatBytes($bytes, $showUnit);

		$this->assertEquals($expected, $formattedBytes);
	}

	public static function formatBytesProvider(): array
	{
		return [
			'happy path' =>
			[
				'bytes' => 13516800,
				'showUnit' => true,
				'expected' => '12.8906 MB',
			],
			'no unit' =>
			[
				'bytes' => 666666666666,
				'showUnits' => false,
				'expected' =>'620.8817',
			],
		];
	}

	/**
	 * @dataProvider truncateTextProvider
	 */
	public function testTruncateText(
		string $stringToTruncate,
		int $limit,
		string $break,
		string $pad,
		string $expected
	): void {
		$truncatedString = $this->truncateText($stringToTruncate, $limit, $break, $pad);

		$this->assertEquals($expected, $truncatedString);
	}

	public static function truncateTextProvider(): array
	{
		return [
			'happy path' =>
			[
				'stringToTruncate' => 'Contritium praecedit superbia',
				'limit' => 8,
				'break' => ' ',
				'pad' => '...',
				'expected' => 'Contritium...',
			],
			'smaller than limit' =>
			[
				'stringToTruncate' => 'Fidite Nemini',
				'limit' => 666,
				'break' => '',
				'pad' => '',
				'expected' => 'Fidite Nemini',
			],
			'different pad' =>
			[
				'stringToTruncate' => 'Mendacem memorem esse oportet',
				'limit' => 12,
				'break' => ' ',
				'pad' => '---',
				'expected' => 'Mendacem memorem---',
			],
			'no string' =>
			[
				'stringToTruncate' => '',
				'limit' => 0,
				'break' => '',
				'pad' => '',
				'expected' => '',
			],
		];
	}

	/**
	 * @dataProvider timeElapsedProvider
	 */
	public function testTimeElapsed(int $timeInSeconds, string $expected): void
	{
		$timeAgo = $this->timeElapsed($timeInSeconds);

		$this->assertEquals($expected, $timeAgo);
	}

	public static function timeElapsedProvider(): array
	{
		return [
			'years ago' =>
			[
				'timeInSeconds' => time() - 60489000,
				'expected' => '2 years ago',
			],
			'hours' =>
			[
				'timeInSeconds' => time() - 82800,
				'expected' => '23 hours ago',
			],
			'minute' =>
			[
				'timeInSeconds' => time() - 62,
				'expected' => '1 minute ago',
			],
		];
	}
}
