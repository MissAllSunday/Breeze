<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Likes;

use Breeze\Service\LikesServiceInterface;
use Breeze\Service\MoodServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Validate\Validations\ValidateData;

abstract class ValidateLikes extends ValidateData
{
	protected UserServiceInterface $userService;

	protected MoodServiceInterface $moodService;

	protected LikesServiceInterface $likesService;

	public function __construct(
		UserServiceInterface $userService,
		LikesServiceInterface $moodService
	) {
		$this->likesService = $moodService;

		parent::__construct($userService);
	}
}
