<?php

declare(strict_types=1);


namespace Breeze\Util;

class Permissions
{
	public static function isNotGuest(string $errorTextKey): void
	{
		is_not_guest($errorTextKey);
	}

	public static function isAllowedTo(string $permissionName): bool
	{
		return true;
	}
}
