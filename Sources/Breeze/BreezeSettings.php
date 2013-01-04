<?php

/**
 * BreezeSettings
 *
 * The purpose of this file is to extract the settings and text strings from the SMF arrays for a better and cleaner handling
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2012, Jessica González
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
 * Portions created by the Initial Developer are Copyright (c) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class BreezeSettings
{
	private static $_instance;
	protected $_settings = array();
	private $_pattern;

	public function __construct()
	{
		$this->_pattern = Breeze::$name .'_';
		$this->doExtract();
	}

	public function doExtract()
	{
		global $modSettings;

		foreach ($modSettings as $key => $value)
			if (strstr($key, $this->_pattern) != false)
				$this->_settings[$key] = $modSettings[$key];
	}

	public function extract()
	{
		global $modSettings;

		$this->_settings = $modSettings;
	}

	/* Return true if the value do exist, false otherwise, O RLY? */
	public function enable($var)
	{
		if (empty($this->_settings))
			$this->extract();

		if (!empty($this->_settings[$this->_pattern . $var]))
			return true;

		else
			return false;
	}

	/* Get the requested setting  */
	public function getSetting($var)
	{
		if (empty($this->_settings))
			$this->doExtract();

		if (!empty($this->_settings[$this->_pattern . $var]))
			return $this->_settings[$this->_pattern . $var];

		else
			return false;
	}
}