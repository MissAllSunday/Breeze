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

	/**
	 * Records a technical detail in the forum's error log.
	 *
	 * Use this for information that is useful to a developer debugging the
	 * install (class names, column names, query shapes) but must never reach
	 * the user: anything thrown back to the client carries a language string
	 * from Breeze.english.php instead.
	 */
	public function logMessage(string $message): void
	{
		if ($message === '' || $message === '0') {
			return;
		}

		\log_error($message);
	}
}
