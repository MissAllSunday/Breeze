<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Mood;

use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class GetActiveMoods extends BaseActions implements ValidateDataInterface
{
	protected const PARAMS = [];

	protected const SUCCESS_KEY = 'moodCreated';

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
	}

	public function isValid(): void
	{
	}
}
