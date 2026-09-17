<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Comment;

use Breeze\Util\Validate\Validations\ValidateActions;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class ValidateComment extends ValidateActions implements ValidateActionsInterface
{
	public function __construct(
		protected DeleteComment $deleteComment,
		protected PostComment $postComment
	) {
	}

	/**
	 * Sub-action names mirror CommentController::SUB_ACTIONS. Both are mutating
	 * actions that read from `$this->data`, so neither is a no-op.
	 *
	 * @return array<string, ValidateDataInterface | null>
	 */
	public function validators(): array
	{
		return [
			'postComment' => $this->postComment,
			'deleteComment' => $this->deleteComment,
		];
	}
}
