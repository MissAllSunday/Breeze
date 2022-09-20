<?php

declare(strict_types=1);


namespace Breeze\Util;

class Json
{
	public static function decode(string $jsonString): array
	{
		return smf_json_decode($jsonString, true);
	}

	public static function encode($data): string
	{
		return json_encode($data);
	}

	public static function isJson(string $string): bool
	{
		json_decode($string);

		return (json_last_error() === \JSON_ERROR_NONE);
	}
}
