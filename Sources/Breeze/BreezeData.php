<?php

/**
 * BreezeData
 *
 * The purpose of this file is get, sanitize and return data from superglobals
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
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

class BreezeData
{
	protected $_request;
	protected $_types = array();

	/**
	 * BreezeData::__construct()
	 *
	 * @param string $type
	 * @return
	 */
	public function __construct($type)
	{
		$this->_types = array('request' => $_REQUEST, 'get' => $_GET, 'post' => $_POST);

		$this->_request = (empty($type) || !isset($this->_types[$type])) ? $_REQUEST : $this->_types[$type];
	}

	/**
	 * BreezeData::get()
	 *
	 * @param mixed $value
	 * @return
	 */
	public function get($value)
	{
		if ($this->validate($value))
			return $this->sanitize($this->_request[$value]);
		else
			return false;
	}

	/**
	 * BreezeData::getRaw()
	 *
	 * @param mixed $value
	 * @return
	 */
	public function getRaw($value)
	{
		if (isset($this->_request[$value]))
			return $this->_request[$value];

		else
			return false;
	}

	public function getAll()
	{
		return $this->_request;
	}

	/**
	 * BreezeData::validate()
	 *
	 * @param mixed $var
	 * @return
	 */
	public function validate($var)
	{
		if (isset($this->_request[$var]))
			return true;
		else
			return false;
	}

	/**
	 * BreezeData::validateBody()
	 *
	 * @param mixed $var
	 * @return
	 */
	public function validateBody($var)
	{
		if (!isset($this->_request[$var]) || empty($this->_request[$var]))
			return false;

		// You cannot post just spaces
		if (ctype_space($this->_request[$var]) || $this->_request[$var] == '')
			return false;

		elseif (isset($this->_request[$var]) && !empty($this->_request[$var]) && !
			ctype_space($this->_request[$var]))
			return true;

		else
			return false;
	}

	/**
	 * BreezeData::unsetVar()
	 *
	 * @param mixed $var
	 * @return
	 */
	public function unsetVar($var)
	{
		unset($this->_request[$var]);
	}

	/**
	 * BreezeData::sanitize()
	 *
	 * @param mixed $var
	 * @return
	 */
	public function sanitize($var)
	{
		if (is_array($var))
			return $var;

		if (get_magic_quotes_gpc())
			$var = stripslashes($var);

		if (is_numeric($var))
			$var = (int)trim($var);

		elseif (is_string($var))
			$var = trim(strtr(htmlspecialchars($var, ENT_QUOTES), array(
				"\r" => '<br />',
				"\n" => '<br />',
				"\t" => '&nbsp;&nbsp;&nbsp;&nbsp;')));

		else
			$var = 'error_' . $var;

		return $var;
	}
}
