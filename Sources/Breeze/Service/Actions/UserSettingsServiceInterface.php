<?php

declare(strict_types=1);

namespace Breeze\Service\Actions;

interface UserSettingsServiceInterface extends ActionsServiceInterface
{
	public function save(array $userSettings, int $userId): bool;

	public function setMessage(string $message, string $type = 'info'): array;

	public function getMessage(): array;
}
