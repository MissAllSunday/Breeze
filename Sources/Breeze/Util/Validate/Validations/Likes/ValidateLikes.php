<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Likes;

use Breeze\Util\Validate\Validations\ValidateActions;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;

class ValidateLikes extends ValidateActions implements ValidateActionsInterface
{
	public function __construct(protected Like $like)
	{
	}
}
