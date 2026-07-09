<?php

declare(strict_types=1);

namespace Breeze\Traits;

use Exception;

trait LogTrait
{
	public function logError(Exception $exception): void
	{
		\log_error($exception->getMessage());
	}
}
