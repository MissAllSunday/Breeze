<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Util\Validate\Validations\ValidateActions;

class ValidateStatus extends ValidateActions
{
	public function __construct(
		protected DeleteStatus $deleteStatus,
		protected PostStatus $postStatus,
		protected StatusByProfile $statusByProfile
	) {
	}
}
