<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Comment;

use Breeze\Util\Validate\Validations\ValidateActions;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;

class ValidateComment extends ValidateActions implements ValidateActionsInterface
{
	public function __construct(
		protected DeleteComment $deleteComment,
		protected PostComment $postComment
	) {
	}
}
