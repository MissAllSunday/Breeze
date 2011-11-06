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

class Breeze_Globals extends Breeze
{
	public function __construct()
	{
		$this->get = $_GET;
		$this->post = $_POST;
    }

	public function Get($index)
	{

		if (self::Is_Set($this->get[$index]))
			$this->get[$index] = self::Sanitize($this->get[$index]);

		return $this->get[$index];
	}

	public function Post($index)
	{
		if (self::Is_Set($this->post[$index]) == true)
			return self::Sanitize($this->post[$index]);
	}

	public static function Is_Set($var)
	{
		if (isset($var) && !empty($var))
		return true;

		else
			return false;
	}

	public static function Sanitize($var)
	{
		global $smcFunc;

		if (is_string($var) == true)
		{
			$var = $smcFunc['htmlspecialchars']($var, ENT_QUOTES);
			$var = $smcFunc['htmltrim']($var, ENT_QUOTES);
		}
		if (is_int($var) == true)
			 $var = (int) $var;

			 return $var;
	}
}
?>