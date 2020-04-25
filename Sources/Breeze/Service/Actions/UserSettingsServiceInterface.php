<?php

declare(strict_types=1);

namespace Breeze\Service\Actions;

interface UserSettingsServiceInterface extends ActionsServiceInterface
{
	public const ACTION = 'profile';
	public const AREA = 'breezeSettings';
	public const TEMPLATE = 'UserSettings';
}
