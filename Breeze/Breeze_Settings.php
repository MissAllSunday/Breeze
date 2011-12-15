<?php

/**
 * Breeze_
 * 
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://missallsunday.com code.
 *
 * The Initial Developer of the Original Code is
 * Jessica González.
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
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