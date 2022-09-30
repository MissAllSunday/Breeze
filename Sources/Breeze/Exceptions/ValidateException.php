<?php

declare(strict_types=1);

namespace Breeze\Exceptions;

class ValidateException extends \Exception
{
	public const STATUS_CODE = 404;

	public function getResponseCode(): int
	{
		return static::STATUS_CODE;
	}
}
