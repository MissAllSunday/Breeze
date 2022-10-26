<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Mood;

use Breeze\Util\Validate\Validations\ValidateData;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

abstract class ValidateMood extends ValidateData implements ValidateDataInterface
{
	public function __construct(
		protected DeleteMood $deleteMood,
		protected GetActiveMoods $getActiveMoods,
		protected GetAllMoods $getAllMoods,
		protected PostMood $postMood,
		protected SetUserMood $setUserMood
	) {
	}
}
