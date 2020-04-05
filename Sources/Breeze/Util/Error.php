<?php

declare(strict_types=1);


namespace Breeze\Util;

use Breeze\Breeze;

class Error
{
	private const ERROR_KEY = Breeze::PATTERN . 'error_';

	public static function show(string $errorTextKey): void
	{
		fatal_lang_error(self::ERROR_KEY . $textKey, false);
	}
}
