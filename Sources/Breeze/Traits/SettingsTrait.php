<?php

declare(strict_types=1);


namespace Breeze\Traits;

use Breeze\Breeze;

trait SettingsTrait
{
	public function getSetting(string $settingName, $fallBack = false): mixed
	{
		$modSettings = $this->global('modSettings');

		if ($settingName === '' || $settingName === '0') {
			return $fallBack;
		}

		return $this->isEnable($settingName) ? $modSettings[Breeze::PATTERN . $settingName] : $fallBack;
	}

	public function isEnable(string $settingName): bool
	{
		$modSettings = $this->global('modSettings');

		return !empty($modSettings[Breeze::PATTERN . $settingName]);
	}

	public function modSetting(string $settingName, $fallBack = false): mixed
	{
		$modSettings = $this->global('modSettings');

		if ($settingName === '' || $settingName === '0') {
			return $fallBack;
		}

		return empty($modSettings[$settingName]) ? $fallBack : $modSettings[$settingName];
	}

	public function global(string $variableName): mixed
	{
		return $GLOBALS[$variableName] ?? false;
	}

	public function setGlobal($globalName, $globalValue): void
	{
		$GLOBALS[$globalName] = $globalValue;
	}

	public function requireOnce(string $fileName, string $dir = ''): void
	{
		if ($fileName === '' || $fileName === '0') {
			return;
		}

		require_once($this->global('sourcedir') . '/' . $fileName . '.php');
	}

	public function setTemplate(string $templateName): void
	{
		loadtemplate($templateName);
	}
}
