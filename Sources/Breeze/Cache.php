<?php


use Breeze\Breeze as Breeze;

trait Cache
{
	public function getCache(string $key, int $timeToLive = 360): ?array
	{
		return cache_get_data(
			Breeze::PATTERN . $key,
			$timeToLive
		);
	}

	public function setCache(string $key, $data, $timeToLive = 360): void
	{
		cache_put_data(Breeze::PATTERN . $key, $data, $timeToLive);
	}

}