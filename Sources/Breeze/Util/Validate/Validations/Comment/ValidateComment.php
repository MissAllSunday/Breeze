<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Comment;

use Breeze\Service\CommentServiceInterface;
use Breeze\Service\StatusServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Validate\Validations\ValidateData;

abstract class ValidateComment extends ValidateData
{
	protected StatusServiceInterface $statusService;

	protected CommentServiceInterface $commentService;

	public function __construct(
		UserServiceInterface $userService,
		StatusServiceInterface $statusService,
		CommentServiceInterface $commentService
	) {
		$this->commentService = $commentService;
		$this->statusService = $statusService;

		parent::__construct($userService);
	}
}
