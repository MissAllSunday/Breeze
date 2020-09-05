<?php

declare(strict_types=1);

namespace Breeze\Traits;

use Breeze\Breeze as Breeze;

trait CacheTrait
{
	public function getCache(string $key, int $timeToLive = 360): ?array
	{
		return cache_get_data(
			$this->buildKey($key),
			$timeToLive
		);
	}

	public function setCache(string $key, ?array $data, int $timeToLive = 360): void
	{
		cache_put_data($this->buildKey($key), $data, $timeToLive);
	}

	private function buildKey(string $key): string
	{
		$class_namespaces = explode('\\', $key);

		return Breeze::PATTERN . str_replace('::', '_', end($class_namespaces));
	}
}
