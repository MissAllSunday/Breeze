<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Mood;

use Breeze\Service\MoodServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Validate\Validations\ValidateData;

abstract class ValidateMood extends ValidateData
{
	protected UserServiceInterface $userService;

	protected MoodServiceInterface $moodService;

	public function __construct(
		UserServiceInterface $userService,
		MoodServiceInterface $moodService
	) {
		$this->moodService = $moodService;

		parent::__construct($userService);
	}
}
