<?php

/**
 * Breeze_
 * 
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0
 * @author Jessica Gonzalez <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011, Jessica Gonzalez
 * @license http://mozilla.org/MPL/2.0/
 */

/**
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License version 2.0 (the \License"). You can obtain a copy of the
 * License at http://mozilla.org/MPL/2.0/.
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