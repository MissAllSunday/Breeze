<?php

/**
 * BreezeSettings
 *
 * The purpose of this file is to extract the text strings from the language files
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

class BreezeText
{
	private static $_instance;
	protected $_text = array();


	private function __construct()
	{
		$this->extract();
	}

	public static function getInstance()
	{
		if (!self::$_instance)
		 {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function extract()
	{
		global $txt;

		loadLanguage(Breeze::$BreezeName);

		$this->_pattern = '/BreezeMod_/';

		/* Get only the settings that we need */
		if (($this->_text = cache_get_data('Breeze:text', 360)) == null)
		{
			foreach ($txt as $kt => $vt)
				if (preg_match($this->_pattern, $kt))
				{
					$kt = str_replace('BreezeMod_', '', $kt);

					/* Done? then populate the new array */
					$this->_text[$kt] = $vt;
				}

			cache_put_data('Breeze:text', $this->_text, 360);
		}
	}

	/* Get the requested setting  */
	public function getText($var)
	{
		if (!empty($this->_text[$var]))
			return $this->_text[$var];

		else
			return false;
	}
}