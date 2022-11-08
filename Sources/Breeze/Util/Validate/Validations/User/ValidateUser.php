<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\User;

use Breeze\Util\Validate\Validations\ValidateActions;

class ValidateUser extends ValidateActions
{
	public function __construct(
		protected UserSettings $userSettings
	) {
	}
}
