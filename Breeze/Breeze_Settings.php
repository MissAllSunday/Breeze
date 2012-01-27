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
	private $Settings;
	private $Text;

	private function __construct()
	{
		$this->Extract();
	}

	public static function getInstance()
	{
		if (!self::$instance)
		 {
			self::$instance = new Breeze_Settings();
		}
		return self::$instance;
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
				$this->matchesSettings[$km] = $vm;
			}


		$this->Settings = $this->matchesSettings;

		/* Again, this time for $txt. */
		foreach ($txt as $kt => $vt)
			if (preg_match($this->pattern, $kt))
			{
				$kt = str_replace('BreezeMod_', '', $kt);
				$this->matchesText[$kt] = $vt;
			}

		$this->Text = $this->matchesText;

		/* Done? then we don't need this anymore */
		if (!empty($this->Text) && !empty($this->Settings))
		{
			unset($this->matchesText);
			unset($this->matchesSettings);
		}
	}

	/* Return true if the value do exist, false otherwise, O RLY? */
	public function Enable($var)
	{
		if (!empty($this->Settings[$var]))
			return true;
		else
			return false;
	}

	/* Get the requested setting  */
	public function GetSetting($var)
	{
		if (!empty($this->Settings[$var]))
			return $this->Settings[$var];

		else
			return false;
	}

	public function GetText($var)
	{
		if (!empty($this->Text[$var]))
			return $this->Text[$var];

		else
			return false;
	}
}