<?php

/**
 * @package breeze mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2011 Suki
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Breeze_Settings
{
	private static $instance;
	private $settings;

	private function __construct()
	{
		 global $modSettings;

		/* Lets get all our settings from $modSettings */
		$matches = array();
		$pattern = '/breeze_/';

		foreach ($modSettings as $k => $v)
			if (preg_match($pattern, $k))
				$matches[$k] = $v;

		$this->settings = $matches;
	}

	public static function getInstance()
	{
		if (!self::$instance)
		 {
			self::$instance = new Breeze_Settings();
		}
		return self::$instance;
	}

	public function enable($var)
	{
		if (!empty($this->settings[$var]))
			return true;
		else
			return false;
	}

	public function get($var)
	{
		if (!empty($this->settings[$var]))
			return $this->settings[$var];
		else
			return false;
	}
}
?>