<?php

declare(strict_types=1);

namespace Breeze\Util;

interface JsonInterface
{
	public static function decode(string $jsonString): array;

	public static function encode($data): string;

	public static function isJson(string $string): bool;
}
