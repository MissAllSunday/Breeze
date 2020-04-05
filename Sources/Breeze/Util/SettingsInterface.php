<?php


namespace Breeze\Util;


use Breeze\Breeze;

interface SettingsInterface
{
	public function get(string $settingName, $fallBack = false);

	public function enable(string $settingName): bool;

	public function modSetting(string $settingName, $fallBack = false);

	public function isJson(string $string): bool;

	public function requireOnce(string $fileName, string $dir = ''): void;

	public function setTemplate(string $templateName): void;

	public function redirect(string $urlName): void;

	public function global(string $variableName);

	public function setGlobal($globalName, $globalValue): void;
}