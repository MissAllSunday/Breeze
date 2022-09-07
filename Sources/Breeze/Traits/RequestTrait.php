<?php

declare(strict_types=1);


namespace Breeze\Traits;

use HTMLPurifier;
use HTMLPurifier_Config;

trait RequestTrait
{
	private mixed $request;

	public function init(): void
	{
		$this->request = $_REQUEST;
	}

	public function getRequest(string $variableName, $defaultValue = null)
	{
		$this->init();

		return !empty($this->request[$variableName]) ?
			$this->sanitize($this->request[$variableName]) : ($defaultValue ?? false);
	}

	public function setPost(string $variableName, $variableValue): void
	{
		$_POST[$variableName] = $variableValue;
	}

	public function setGet(string $variableName, $variableValue): void
	{
		$_GET[$variableName] = $variableValue;
	}

	public function isRequestSet(string $variableName): bool
	{
		$this->init();

		return isset($this->request[$variableName]);
	}

	public function sanitize($variable): mixed
	{
		$this->init();

		$smcFunc = $this->getSmcFunc();

		if (is_array($variable)) {
			foreach ($variable as $key => $variableValue) {
				$variable[$key] = $this->sanitize($variableValue);
			}

			return $variable;
		}

		$var = $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($variable, \ENT_QUOTES));

		if (ctype_digit($var)) {
			$var = (int) $var;
		}

		if (empty($var)) {
			$var = false;
		}

		return $var;
	}

	private function getSmcFunc(): array
	{
		return $GLOBALS['smcFunc'];
	}

	private function getPurifier(): HTMLPurifier
	{
		$config = HTMLPurifier_Config::createDefault();

		return HTMLPurifier::getInstance($config);
	}
}
