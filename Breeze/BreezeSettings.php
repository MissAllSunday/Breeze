<?php

/**
 * Breeze_
 *
 * The purpose of this file is to extract the settings and text strings from the SMF arrays for a better and cleaner handling
 * @package Breeze mod
 * @version 1.0 Beta 2
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
	private $_settings;
	private $_text;

	private function __construct()
	{
		$this->Extract();
	}

	public static function getInstance()
	{
		if (!self::$_instance)
		 {
			self::$_instance = new BreezeSettings();
		}
		return self::$_instance;
	}

	public function Extract()
	{
		global $txt, $modSettings;

		loadLanguage('Breeze');

		$this->pattern = '/BreezeMod_/';
		$this->matchesSettings = array();

		/* Get only the settings that we need */
		foreach ($modSettings as $km => $vm)
			if (preg_match($this->pattern, $km))
			{
				$km = str_replace('BreezeMod_', '', $km);

				/* Hickjack this to convert the string to a timestamp */
				if ($km == 'admin_limit_timeframe')
					$vm = strtotime('-1 '.$vm);

				/* Done? then populate the new array */
				$this->matchesSettings[$km] = $vm;
			}

		$this->_settings = $this->matchesSettings;

		/* Again, this time for $txt. */
		foreach ($txt as $kt => $vt)
			if (preg_match($this->pattern, $kt))
			{
				$kt = str_replace('BreezeMod_', '', $kt);
				$this->matchesText[$kt] = $vt;
			}

		$this->_text = $this->matchesText;

		/* Done? then we don't need this anymore */
		if (!empty($this->_text) && !empty($this->_settings))
		{
			unset($this->matchesText);
			unset($this->matchesSettings);
		}
	}

	/* Return true if the value do exist, false otherwise, O RLY? */
	public function enableSetting($var)
	{
		if (!empty($this->_settings[$var]))
			return true;
		else
			return false;
	}

	/* Get the requested setting  */
	public function getSetting($var)
	{
		if (!empty($this->_settings[$var]))
			return $this->_settings[$var];

		else
			return false;
	}

	public function getText($var)
	{
		if (!empty($this->_text[$var]))
			return $this->_text[$var];

		else
			return false;
	}
}