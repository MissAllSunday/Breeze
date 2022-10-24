<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Likes;

use Breeze\Util\Validate\Validations\ValidateData;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

abstract class ValidateLikes extends ValidateData implements ValidateDataInterface
{
	public function __construct(protected Like $like)
	{
	}
}
