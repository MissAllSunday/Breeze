<?php

declare(strict_types=1);

namespace Breeze\Service\Actions;

interface UserSettingsServiceInterface extends ActionsServiceInterface
{
	public const ACTION = 'profile';

	public const AREA = 'breezeSettings';

	public const TEMPLATE = 'UserSettings';

	public function save(array $userSettings, int $userId): bool;

	public function setMessage(string $message, string $type = 'info'): array;

	public function getMessage(): array;
}
