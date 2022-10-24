<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Comment;

use Breeze\Util\Validate\Validations\ValidateData;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

abstract class ValidateComment extends ValidateData implements ValidateDataInterface
{
	public function __construct(
		protected DeleteComment $deleteComment,
		protected PostComment $postComment
	) {
	}
}
