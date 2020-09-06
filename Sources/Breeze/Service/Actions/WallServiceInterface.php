<?php

declare(strict_types=1);


namespace Breeze\Service\Actions;

interface WallServiceInterface extends ActionsServiceInterface
{
	public const ACTION = 'breeze';

	public function init(array $subActions):void;

	public function defaultSubActionContent(string $subTemplate, array $params, string $smfTemplate);

	public function isAllowedToSeePage(bool $redirect = false): bool;

	public function getStatus(int $userId): array;

	public function isCurrentUserOwner(): bool;

	public function getUsersToLoad(): array;

	public function setUsersToLoad(array $usersToLoad): void;

	public function loadCSS(): void;
}
