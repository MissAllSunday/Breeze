<?php

declare(strict_types=1);

namespace Breeze\Util;

use DateTimeImmutable;

class Time implements TimeInterface
{
	public static function timeFormat(int $timeStamp): string
	{
		return timeformat($timeStamp);
	}

	public static function from(?DateTimeImmutable $dateTime): string
	{
		return timeformat($dateTime instanceof \DateTimeImmutable ? $dateTime->getTimestamp() : '');
	}
}
