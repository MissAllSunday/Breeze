<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Util\Permissions;

class PermissionsService extends BaseService implements ServiceInterface
{
	public const IDENTIFIER = 'Permissions';

	public function hookPermissions(&$permissionGroups, &$permissionList): void
	{
		$this->setLanguage(Breeze::NAME . self::IDENTIFIER);

		$permissionGroups['membergroup']['simple'] = ['breeze_per_simple'];
		$permissionGroups['membergroup']['classic'] = ['breeze_per_classic'];

		foreach (Permissions::ALL_PERMISSIONS as $permissionName) {
			$permissionList['membergroup']['breeze_' . $permissionName] = [
				false,
				'breeze_per_classic',
				'breeze_per_simple'];
		}
	}

	public function get(string $permissionName): bool
	{
		return allowedTo($permissionName);
	}
}
