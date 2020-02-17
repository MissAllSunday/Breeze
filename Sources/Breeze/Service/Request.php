<?php

declare(strict_types=1);


namespace Breeze\Service;

class Request extends Base
{
	private $request;

    public function __construct()
	{
		$this->request = $_REQUEST;
    }

	public function get(string $variableName)
	{
	    return isset($this->request[$variableName]) ? $this->sanitize($this->request[$variableName]) : false;
	}

	public function sanitize($variable)
	{
		$smcFunc = $this->global('smcFunc');

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
}
