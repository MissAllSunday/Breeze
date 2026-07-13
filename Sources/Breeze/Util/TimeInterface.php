<?php

declare(strict_types=1);

namespace Breeze\Util;

use DateTimeImmutable;

interface TimeInterface
{
	public static function timeFormat(int $timeStamp): string;

	public static function from(?DateTimeImmutable $dateTime): string;
}
