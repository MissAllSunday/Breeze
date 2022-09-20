<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Comment;

use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\Validations\ValidateData;

abstract class ValidateComment extends ValidateData
{
	protected StatusRepositoryInterface $statusRepository;

	protected CommentRepositoryInterface $commentService;

	public function getCurrentUserInfo(): array
	{
		return $this->global('user_info');
	}
}
