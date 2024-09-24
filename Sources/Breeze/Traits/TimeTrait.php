<?php

namespace Breeze\Traits;

trait TimeTrait
{
	public function timeFormat(int $timeStamp): string
	{
		return timeformat($timeStamp);
	}
}
