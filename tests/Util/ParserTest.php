<?php

declare(strict_types=1);

namespace Breeze\Util;

use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
	public function testBbcWithSimpleText(): void
	{
		$content = 'Hello World';
		$result = Parser::bbc($content);

		$this->assertEquals($content, $result);
	}

	public function testBbcWithEmptyString(): void
	{
		$content = '';
		$result = Parser::bbc($content);

		$this->assertEquals('', $result);
	}

	public function testBbcCallsGlobalFunction(): void
	{
		// The parse_bbc function is mocked in bootstrap.php to return the content as-is
		$content = '[b]Bold text[/b]';
		$result = Parser::bbc($content);

		// Since parse_bbc is mocked to return content unchanged
		$this->assertEquals($content, $result);
	}
}
