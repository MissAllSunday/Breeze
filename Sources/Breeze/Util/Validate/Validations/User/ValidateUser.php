<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\User;

use Breeze\Util\Validate\Validations\ValidateActions;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;

class ValidateUser extends ValidateActions implements ValidateActionsInterface
{
	public function __construct(
		protected UserSettings $userSettings
	) {
	}
}
