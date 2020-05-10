<?php

declare(strict_types=1);

namespace Breeze\Traits;

use Breeze\Breeze;

trait PersistenceTrait
{
	private static $notice = 'notice';
	
	public function setMessage(string $message, string $type = 'info'): array
	{
		if (empty($message))
			return [];

		if (!isset($_SESSION[Breeze::NAME]))
			$_SESSION[Breeze::NAME] = [];

		$_SESSION[Breeze::NAME][self::$notice] = [
			'message' => $message,
			'type' => $type,
		];

		return $_SESSION[Breeze::NAME][self::$notice];
	}

	public function getMessage(): array
	{
		if (empty($_SESSION[Breeze::NAME][self::$notice]))
			return [];

		$response = $_SESSION[Breeze::NAME][self::$notice];
		unset($_SESSION[Breeze::NAME][self::$notice]);

		return $response;
	}

	public function setPersistenceValue(string $valueName, $value): void
	{
		$_SESSION[Breeze::NAME][$valueName] = $value;
	}

	public function getPersistenceValue(string $valueName)
	{
		return empty($_SESSION[Breeze::NAME][$valueName]) ? null : $_SESSION[Breeze::NAME][$valueName];
	}

	public function unsetPersistenceValue(string $valueName): void
	{
		unset($_SESSION[Breeze::NAME][$valueName]);
	}
}
