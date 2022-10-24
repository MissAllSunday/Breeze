<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Util\Validate\Validations\ValidateData;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

abstract class ValidateStatus extends ValidateData implements ValidateDataInterface
{
	public function __construct(
		protected DeleteStatus $deleteStatus,
		protected PostStatus $postStatus,
		protected StatusByProfile $statusByProfile
	) {
	}
}
