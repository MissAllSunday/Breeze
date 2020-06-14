<?php

declare(strict_types=1);

namespace Breeze\Traits;

use Breeze\Breeze as Breeze;

trait CacheTrait
{
	public function getCache(string $key, int $timeToLive = 360): ?array
	{
		return cache_get_data(
			Breeze::PATTERN . $key,
			$timeToLive
		);
	}

	public function setCache(string $key, ?array $data, int $timeToLive = 360): void
	{
		cache_put_data(Breeze::PATTERN . $key, $data, $timeToLive);
	}
}
