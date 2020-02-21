<?php

declare(strict_types=1);

namespace Breeze\Service;

class BaseService
{
	public function global(string $variableName)
	{
		return $GLOBALS[$variableName] ?? false;
	}

	public function setGlobal($globalName, $globalValue): void
	{
		$GLOBALS[$globalName] = $globalValue;
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
}
