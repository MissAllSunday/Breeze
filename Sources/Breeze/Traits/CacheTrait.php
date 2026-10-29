<?php

declare(strict_types=1);

namespace Breeze\Traits;

use Breeze\Breeze as Breeze;
use Breeze\Entity\EntityInterface;

trait CacheTrait
{
	public function getCache(string $key, int $timeToLive = 360): mixed
	{
		$data = cache_get_data(
			$this->buildKey($key),
			$timeToLive
		);

		return $data ?? [];
	}

	public function setCache(string $key, array | EntityInterface | int | null $data, int $timeToLive = 360): void
	{
		cache_put_data($this->buildKey($key), $data, $timeToLive);
	}

	/**
	 * Build a cache key namespaced by the using class, so two classes that
	 * happen to share a key shape (e.g. `getById`) cannot poison each other.
	 */
	private function buildKey(string $key): string
	{
		static $shortNames = [];

		$class = static::class;

		if (!isset($shortNames[$class])) {
			$namespaces = explode('\\', $class);
			$shortNames[$class] = end($namespaces);
		}

		return Breeze::PATTERN . $shortNames[$class] . '_' . str_replace('::', '_', $key);
	}
}
