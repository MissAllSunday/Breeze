<?php

declare(strict_types=1);

namespace Breeze\Util\Validate;

use Breeze\Exceptions\ValidateException;

class DataNotFoundException extends ValidateException
{
	final public const int STATUS_CODE = 404;
}
