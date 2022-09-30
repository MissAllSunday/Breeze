<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Exceptions\ValidateException;

class InvalidDataException extends ValidateException
{
	final public const STATUS_CODE = 400;
}
