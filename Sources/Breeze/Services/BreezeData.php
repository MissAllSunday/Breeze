<?php

declare(strict_types=1);


namespace Breeze\Service;

class Data extends Base
{
	protected $request;

    public function __construct()
	{
		$this->request = $_REQUEST;
    }

	public function get(string $value)
	{
	    return isset($this->request[$value]) ? $this->sanitize($this->request[$value]) : false;
	}

	public function getAll()
	{
		return array_map(function($k, $v)
		{
			return $this->sanitize($v);
		}, $this->request);
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

		$var = (string) $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($variable, \ENT_QUOTES));

		if (ctype_digit($var))
			$var = (int) $var;

		if (empty($var))
			$var = false;

		return $var;
	}
}
