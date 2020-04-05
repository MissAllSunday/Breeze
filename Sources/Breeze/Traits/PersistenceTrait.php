<?php

declare(strict_types=1);

namespace Breeze\Traits;

use Breeze\Breeze;

trait PersistenceTrait
{
	public function setMessage(string $message, string $type = 'info'): array
	{
		if (empty($message))
			return [];

		if (!isset($_SESSION[Breeze::NAME]))
			$_SESSION[Breeze::NAME] = [];

		$_SESSION[Breeze::NAME]['notice'] = [
			'message' => $message,
			'type' => $type,
		];

		return $_SESSION[Breeze::NAME]['notice'];
	}

	public function getMessage(): array
	{
		if (empty($_SESSION[Breeze::NAME]['notice']))
			return [];

		$response = $_SESSION[Breeze::NAME]['notice'];
		unset($_SESSION[Breeze::NAME]['notice']);

		return $response;
	}
}
