<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Mood;

use Breeze\Util\Validate\Validations\ValidateActions;

class ValidateMood extends ValidateActions
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
