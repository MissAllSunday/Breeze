<?php

declare(strict_types=1);

namespace Breeze\Util;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class TimeTest extends TestCase
{
	public function testTimeFormatWithValidTimestamp(): void
	{
		$timestamp = 581299200; // 1988-06-01 00:00:00 UTC
		$result = Time::timeFormat($timestamp);

		$this->assertIsString($result);
		$this->assertNotEmpty($result);
		// The timeformat function is mocked in bootstrap.php to return ATOM format
		$this->assertStringContainsString('1988-06', $result);
	}

	public function testTimeFormatWithZeroTimestamp(): void
	{
		$timestamp = 0; // 1970-01-01 00:00:00 UTC
		$result = Time::timeFormat($timestamp);

		$this->assertIsString($result);
		$this->assertStringContainsString('1970-01-01', $result);
	}

	public function testFromWithValidDateTime(): void
	{
		$dateTime = new DateTimeImmutable('1988-06-01 00:00:00');
		$result = Time::from($dateTime);

		$this->assertIsString($result);
		$this->assertNotEmpty($result);
		$this->assertStringContainsString('1988-06-01', $result);
	}

	public function testFromWithNullDateTime(): void
	{
		// When null is passed, Time::from() passes empty string to timeformat
		// which will cause a TypeError, so we expect an exception
		$this->expectException(\TypeError::class);
		Time::from(null);
	}

	public function testFromWithCurrentDateTime(): void
	{
		$dateTime = new DateTimeImmutable();
		$result = Time::from($dateTime);

		$this->assertIsString($result);
		$this->assertNotEmpty($result);
	}
}
