<?php

declare(strict_types=1);

namespace Breeze\Traits;

use Breeze\Breeze;
use Breeze\PermissionsEnum;
use Breeze\Service\PermissionsService;

trait PermissionsTrait
{
	public function isNotGuest(string $errorTextKey): void
	{
		is_not_guest($errorTextKey);
	}

	public function isAllowedTo(string $permissionName): bool
	{
		return allowedTo(PermissionsEnum::isSMFPermission($permissionName) ?
			$permissionName :
			(strtolower(Breeze::PATTERN) . $permissionName));
	}
}
