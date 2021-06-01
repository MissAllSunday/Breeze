<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Likes;

use Breeze\Service\LikeServiceInterface;
use Breeze\Service\MoodServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Validate\Validations\ValidateData;

abstract class ValidateLikes extends ValidateData
{
	protected UserServiceInterface $userService;

	protected MoodServiceInterface $moodService;

	protected LikeServiceInterface $likeService;

	public function __construct(
		UserServiceInterface $userService,
		LikeServiceInterface $likeService
	) {
		$this->likeService = $likeService;

		parent::__construct($userService);
	}

	public static function getNameSpace(): string
	{
		return __NAMESPACE__ . '\\';
	}
}
