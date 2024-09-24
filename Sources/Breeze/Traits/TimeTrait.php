<?php

declare(strict_types=1);

namespace Breeze\Traits;

trait TimeTrait
{
	public function timeFormat(int $timeStamp): string
	{
		return timeformat($timeStamp);
	}
}
