<?php

declare(strict_types=1);

namespace Breeze\Service;

interface UserSettingsServiceInterface extends BaseServiceInterface
{
	public const ACTION = 'userSettings';

	public function init():void;

	public function setSubActionContent(
		string $actionName,
		array $templateParams = [],
		string $smfTemplate = ''
	): void;
}
