<?php

declare(strict_types=1);


namespace Breeze\Service;

interface PermissionsServiceInterface
{
	public const IDENTIFIER = 'Permissions';

	public function hookPermissions(&$permissionGroups, &$permissionList): void;

	public function permissions(int $profileOwner = 0, int $userPoster = 0): array;

	public function isEnable(): array;
}
