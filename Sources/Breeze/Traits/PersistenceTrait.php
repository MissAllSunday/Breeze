<?php

declare(strict_types=1);

namespace Breeze\Traits;

use Breeze\Breeze;

trait PersistenceTrait
{
	public function setMessage($message, $type): void
	{
		if (empty($message) || empty($type))
			return;

		$_SESSION[Breeze::NAME] = [
		    'message' => $message,
		    'type' => $type,
		];
	}

	public function getMessage(): array
	{
		if (empty($_SESSION[Breeze::NAME]))
			return [];

		$response = $_SESSION[Breeze::NAME];
		unset($_SESSION[Breeze::NAME]);

		return $response;
	}
}
