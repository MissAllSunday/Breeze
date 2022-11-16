<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Util\Validate\Validations\ValidateActions;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;

class ValidateStatus extends ValidateActions implements ValidateActionsInterface
{
	public function __construct(
		public DeleteStatus $deleteStatus,
		public PostStatus $postStatus,
		public StatusByProfile $statusByProfile
	) {
	}
}
