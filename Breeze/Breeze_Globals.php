<?php

/**
 * @package breeze mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011 Suki
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Breeze_Globals
{
	private static $instances = array();
	private $request;

	public static function factory($var)
	{
		if (array_key_exists($var, self::$instances))
		{
			return self::$instances[$var];
		}
		return self::$instances[$var] = new Breeze_Globals($var);
	}

	function __construct($var)
	{
		if ($var == 'get')
			$this->request = $_GET;
		elseif ($var == 'post')
			$this->request = $_POST;
		elseif ($var == 'request')
			$this->request = $_REQUEST;

		else
			return;
	}

	function see($value)
	{
		if (isset($this->request[$value]) && self::validate($this->request[$value]))
			return self::Sanitize($this->request[$value]);
		else
			return 'error_' . $value;
	}

	function raw($value)
	{
		if (isset($this->request[$value]))
			return $this->request[$value];
	}

	public static function validate($var)
	{
		if (!empty($var))
			return true;
		else
			return false;
	}

	public static function Sanitize($var)
	{
		if (get_magic_quotes_gpc())
			$var = stripslashes($var);

		if (is_numeric($var))
			$var = (int) $var;

		elseif (is_string($var))
		{
			$var = strtr(htmlspecialchars($var, ENT_QUOTES), array("\r" => '', "\n" => '', "\t" => ''));
			$var = trim($var);
		}

		else
			$var = 'error_' . $var;

		return $var;
	}
}
?>