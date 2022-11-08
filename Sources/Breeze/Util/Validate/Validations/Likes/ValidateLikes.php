<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Likes;

use Breeze\Util\Validate\Validations\ValidateActions;

class ValidateLikes extends ValidateActions
{
	public function __construct(protected Like $like)
	{
	}
}
