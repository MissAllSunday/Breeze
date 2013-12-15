<?php

/**
 * BreezeSettings
 *
 * The purpose of this file is to extract the text strings from the language files
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013 Jessica González
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
	die('No direct access...');

class BreezeText
{
	protected $_text = array();
	private $_pattern;

	public function __construct()
	{
		/* Load the mod's language file */
		loadLanguage(Breeze::$name);

		$this->_pattern = Breeze::$name .'_';
	}

	/**
	 * Get the requested array element.
	 *
	 * @param string the key name for the requested element
	 * @access public
	 * @return mixed
	 */
	public function getText($var)
	{
		global $txt;

		if (empty($var))
			return false;

		if (!empty($txt[$this->_pattern . $var]))
			return $txt[$this->_pattern . $var];

		else
			return false;
	}

	public function getAll()
	{
		global $txt;

		return $txt;
	}
}