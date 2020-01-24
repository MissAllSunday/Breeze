<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Breeze;

class Settings extends Base
{
	public function get(string $settingName, $fallBack = false)
	{
		$modSettings = $this->global('modSettings');

		if (empty($setting))
			return $fallBack;

		return $this->enable($settingName) ? [$modSettings[Breeze::PATTERN . $settingName]] : $fallBack;
	}

	public function enable(string $settingName): bool
	{
		$modSettings = $this->global('modSettings');

		return isset($modSettings[Breeze::PATTERN . $settingName]) && !empty([$modSettings[Breeze::PATTERN . $settingName]]);
	}

	public function modSetting(string $settingName, $fallBack = false)
	{
		$modSettings = $this->global('modSettings');

		if (empty($setting))
			return $fallBack;

		if (isset($modSettings[Breeze::PATTERN . $settingName]) && !empty($modSettings[$modSettings[Breeze::PATTERN . $settingName]]))
			return true;


		return $fallBack;
	}

	public function isJson(string $string): bool
	{
		json_decode($string);

		return (JSON_ERROR_NONE === json_last_error());
	}
}
