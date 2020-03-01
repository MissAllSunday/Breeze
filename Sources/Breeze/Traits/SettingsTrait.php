<?php

declare(strict_types=1);


namespace Breeze\Traits;

use Breeze\Breeze;

trait SettingsTrait
{
	public static $allSettings = [
	    'wall' => 'CheckBox',
	    'general_wall' => 'CheckBox',
	    'pagination_number' => 'Int',
	    'number_alert' => 'Int',
	    'load_more' => 'CheckBox',
	    'activityLog' => 'CheckBox',
	    'kick_ignored' => 'CheckBox',
	    'blockList' => 'TextTrait',
	    'blockListIDs' => 'Array',
	    'buddies' => 'CheckBox',
	    'how_many_buddies' => 'Int',
	    'visitors' => 'CheckBox',
	    'how_many_visitors' => 'Int',
	    'clear_noti' => 'HTML',
	    'aboutMe' => 'TextArea',
	    'cover_height' => 'Int',
	];

	public function global(string $variableName)
	{
		return $GLOBALS[$variableName] ?? false;
	}

	public function setGlobal($globalName, $globalValue): void
	{
		$GLOBALS[$globalName] = $globalValue;
	}

	public function getSetting(string $settingName, $fallBack = false)
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
}
