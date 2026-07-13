<?php

declare(strict_types=1);

namespace Breeze\Util;

interface ComponentsInterface
{
	public function loadUIVars(array $vars = []): void;

	public function loadComponents(array $components = []): void;

	public function loadJavaScriptFile(string $fileName, array $params = [], string $nameIdentifier = ''): void;

	public function loadCSSFile(string $fileName, array $params = [], $nameIdentifier = ''): void;

	public function addJavaScriptVar(string $variable, $value): void;

	public function loadTxtVarsFor(array $components): void;
}
