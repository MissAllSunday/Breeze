<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Likes;

use Breeze\Util\Validate\Validations\ValidateActions;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class ValidateLikes extends ValidateActions implements ValidateActionsInterface
{
	public function __construct(protected Like $like)
	{
	}

	/**
	 * Sub-action names mirror LikesController::SUB_ACTIONS. `like` is a mutating
	 * action that reads from `$this->data`, so it is never a no-op.
	 *
	 * @return array<string, ValidateDataInterface | null>
	 */
	public function validators(): array
	{
		return [
			'like' => $this->like,
		];
	}
}
