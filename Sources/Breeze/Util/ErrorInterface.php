<?php

declare(strict_types=1);

namespace Breeze\Util;

interface ErrorInterface
{
	public static function show(string $errorTextKey): void;
}
