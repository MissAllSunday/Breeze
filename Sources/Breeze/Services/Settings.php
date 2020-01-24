<?php


namespace Breeze\Service;


use Breeze\Breeze;

class Settings
{
	/**
	 * @var Tools
	 */
	protected $tools;

	public function __construct(Tools $tools)
	{
		$this->tools = $tools;
	}

	public function setting(string $settingName, $fallBack = false)
	{
		$modSettings = $this->tools->global('modSettings');

		if (empty($setting))
			return $fallBack;


		return $this->enable($settingName) ? [$modSettings[Breeze::PATTERN . $settingName]] : $fallBack;
	}

	public function enable(string $settingName): bool
	{
		$modSettings = $this->tools->global('modSettings');

		return isset($modSettings[Breeze::PATTERN . $settingName]) && !empty([$modSettings[Breeze::PATTERN . $settingName]]);
	}

	public function modSetting(string $settingName, $fallBack = false)
	{
		$modSettings = $this->tools->global('modSettings');

		if (empty($setting))
			return $fallBack;

		if (isset($modSettings[Breeze::PATTERN . $settingName]) && !empty($modSettings[$modSettings[Breeze::PATTERN . $settingName]]))
			return true;


		return $fallBack;
	}
}