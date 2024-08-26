<?php

declare(strict_types=1);

namespace Breeze\Util\Validate;

use Breeze\Exceptions\ValidateException;

class EmptyDataException extends ValidateException
{
	final public const STATUS_CODE = 204;
}
