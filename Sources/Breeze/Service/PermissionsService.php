<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\PermissionsEnum;
use Breeze\Traits\PermissionsTrait;
use Breeze\Traits\TextTrait;

class PermissionsService
{
	use TextTrait;
	use PermissionsTrait;

	public const IDENTIFIER = 'PermissionsTrait';

	public function hookPermissions(&$permissionGroups, &$permissionList): void
	{
		$this->setLanguage(Breeze::NAME . self::IDENTIFIER);

		$permissionGroups['membergroup']['simple'] = ['breeze_per_simple'];
		$permissionGroups['membergroup']['classic'] = ['breeze_per_classic'];

		foreach (PermissionsEnum::ALL_PERMISSIONS as $permissionName) {
			$permissionList['membergroup']['breeze_' . $permissionName] = [
				false,
				'breeze_per_classic',
				'breeze_per_simple',];
		}
	}
}
