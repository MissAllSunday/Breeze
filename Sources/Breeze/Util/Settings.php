<?php


namespace Breeze\Util;


use Breeze\Breeze;

class Settings implements SettingsInterface
{
	public function get(string $settingName, $fallBack = false)
	{
		$modSettings = $this->global('modSettings');

		if (empty($settingName))
			return $fallBack;

		return $this->enable($settingName) ? $modSettings[Breeze::PATTERN . $settingName] : $fallBack;
	}

	public function enable(string $settingName): bool
	{
		$modSettings = $this->global('modSettings');

		return !empty($modSettings[Breeze::PATTERN . $settingName]);
	}

	public function modSetting(string $settingName, $fallBack = false)
	{
		$modSettings = $this->global('modSettings');

		if (empty($settingName))
			return $fallBack;

		return !empty($modSettings[$settingName]) ? $modSettings[$settingName] : $fallBack;
	}

	public function isJson(string $string): bool
	{
		json_decode($string);

		return (JSON_ERROR_NONE === json_last_error());
	}

	public function requireOnce(string $fileName, string $dir = ''): void
	{
		if (empty($fileName))
			return;

		$sourceDir = !empty($dir) ? $dir : $this->global('sourcedir');

		require_once($sourceDir . '/' . $fileName . '.php');
	}

	public function setTemplate(string $templateName): void
	{
		loadtemplate($templateName);
	}

	public function redirect(string $urlName): void
	{
		if(!empty($urlName))
			redirectexit($urlName);
	}

	public function global(string $variableName)
	{
		return $GLOBALS[$variableName] ?? false;
	}

	public function setGlobal($globalName, $globalValue): void
	{
		$GLOBALS[$globalName] = $globalValue;
	}
}