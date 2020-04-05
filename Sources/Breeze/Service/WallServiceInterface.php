<?php

declare(strict_types=1);


namespace Breeze\Service;

interface WallServiceInterface
{
	public const ACTION = 'breeze';

	public function initPage(): void;

	public function setSubActionContent(
		string $actionName,
		array $templateParams = [],
		string $smfTemplate = ''
	): void;

	public function isAllowedToSeePage(bool $redirect = false): bool;

	public function getStatus(int $userId): void;

	function isCurrentUserOwner(): bool;

	function getUsersToLoad(): array;

	function setUsersToLoad(array $usersToLoad): void;
}
