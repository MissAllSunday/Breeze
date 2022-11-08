<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Comment;

use Breeze\Util\Validate\Validations\ValidateActions;

class ValidateComment extends ValidateActions
{
	public function __construct(
		protected DeleteComment $deleteComment,
		protected PostComment $postComment
	) {
	}
}
