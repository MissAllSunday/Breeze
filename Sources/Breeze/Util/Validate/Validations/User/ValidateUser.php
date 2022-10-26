<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\User;

use Breeze\Util\Validate\Validations\ValidateData;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

abstract class ValidateUser extends ValidateData implements ValidateDataInterface
{
	public function __construct(
		protected UserSettings $userSettings
	) {
	}
}
