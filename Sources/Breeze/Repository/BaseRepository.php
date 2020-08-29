<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Breeze;
use Breeze\Traits\CacheTrait;

abstract class BaseRepository
{
	use CacheTrait;

	public const LIKE_TYPE_STATUS = 'breSta';
	public const LIKE_TYPE_COMMENT = 'breCom';
	protected const TTL = 360;

	public function handleLikes($type, $content): array
	{
		return [];
	}

	public static function getAllTypes(): array
	{
		return [
			self::LIKE_TYPE_STATUS,
			self::LIKE_TYPE_COMMENT
		];
	}

	protected function cacheKey(string $keyName): string
	{
		return str_replace('::', '_', Breeze::PATTERN . $keyName);
	}

	public function cleanCache(string $cacheName): void
	{
		$this->setCache($cacheName, null);
	}
}
