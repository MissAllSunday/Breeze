<?php

declare(strict_types=1);


namespace Breeze\Traits;

trait RequestTrait
{
	private $request;

	public function __construct()
	{
		$this->request = $_REQUEST;
	}

	public function get(string $variableName, $defaultValue = '')
	{
		return isset($this->request[$variableName]) ?
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

	public function isSet(string $variableName)
	{
		return isset($this->request[$variableName]);
	}

	public function sanitize($variable)
	{
		$smcFunc = $this->getSmcFunc();

		if (is_array($variable))
		{
			foreach ($variable as $k => $v)
				$variable[$k] = $this->sanitize($v);

			return $variable;
		}

		$var = (string) $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($variable, ENT_QUOTES));

		if (ctype_digit($var))
			$var = (int) $var;

		if (empty($var))
			$var = false;

		return $var;
	}

	public function __destruct()
	{
		$this->request = [];
	}

	private function getSmcFunc(): array
	{
		return $GLOBALS['smcFunc'];
	}
}
