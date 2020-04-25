<?php

declare(strict_types=1);


namespace Breeze\Traits;

use Breeze\Breeze;

trait SettingsTrait
{
	public function getSetting(string $settingName, $fallBack = false)
	{
		$modSettings = $this->global('modSettings');

		if (empty($settingName))
			return $fallBack;

		return $this->isEnable($settingName) ? $modSettings[Breeze::PATTERN . $settingName] : $fallBack;
	}

	public function isEnable(string $settingName): bool
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

	public function global(string $variableName)
	{
		return $GLOBALS[$variableName] ?? false;
	}

	public function setGlobal($globalName, $globalValue): void
	{
		$GLOBALS[$globalName] = $globalValue;
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

		require_once($this->global('sourcedir') . '/' . $fileName . '.php');
	}

	public function setTemplate(string $templateName): void
	{
		loadtemplate($templateName);
	}

	public function loadCSS(): void
	{
		loadCSSFile('breeze.css', [], 'smf_breeze');
	}

	public function loadJS(): void
	{
		loadJavaScriptFile(Breeze::VUE_CDN, [
			'external' => true,
			'defer' => false,
		], 'breeze_vue');

		loadJavaScriptFile(Breeze::AXIOS_CDN, [
			'external' => true,
			'defer' => false,
		], 'breeze_axios');

		loadJavaScriptFile('https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js', [
			'external' => true,
			'defer' => false,
		], 'breeze_moment');
	}
}
