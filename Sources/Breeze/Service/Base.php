<?php

declare(strict_types=1);

namespace Breeze\Service;

class Base
{
	public function global(string $variableName)
	{
		return $GLOBALS[$variableName] ?? false;
	}

	public function setGlobal($globalName, $globalValue): void
	{
		$GLOBALS[$globalName][$globalVariable] = $globalValue;
	}

	public function requireOnce(string $fileName, string $dir = ''): void
	{
		$sourceDir = $dir ?? $this->global('sourcedir');

		require_once($sourceDir . '/' . $fileName . '.php');
	}

	public function setTemplate(string $templateName): void
	{
		loadtemplate($templateName);
	}
}
