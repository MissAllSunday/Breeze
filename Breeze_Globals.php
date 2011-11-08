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

class Globals
{
	private static $instances = array();
 
	public static function factory($var)
	{
		if (array_key_exists($var, self::$instances))
		{
			return self::$instances[$var];
		}
		return self::$instances[$var] = new Globals($var);
	}

	function __construct($var)
	{
		if ($var == 'get')
			$this->request = $_GET;

		elseif ($var == 'post')
			$this->request = $_POST;
	}

	function see($value)
	{
		return self::Sanitize($this->request[$value]);
	}

	public static function validate($var)
	{
		if (isset($var) && !empty($var) && in_array($var, $_GET) || in_array($var, $_POST))
			return true;

		else
			return false;
	}

	public static function Sanitize($var)
	{
		if (get_magic_quotes_gpc())
			$var = stripslashes($var);

		if (is_string($var))
			$var = trim(htmlspecialchars($var, ENT_QUOTES));
		
		elseif (is_int($var))
			$var = (int) $var;

		return $var;
	}
}
?>