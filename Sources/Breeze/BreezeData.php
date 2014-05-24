<?php

/**
 * BreezeData
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica GonzÃ¡lez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeData
{
	protected $_request;

	/**
	 * BreezeData::__construct()
	 *
	 * @param string $type
	 * @return
	 */
	public function __construct($type)
	{
		$types = array('request' => $_REQUEST, 'get' => $_GET, 'post' => $_POST);

		$this->_request = (empty($type) || !isset($types[$type])) ? $_REQUEST : $types[$type];

		unset($types);
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
		global $sourcedir;

		// You cannot post just spaces
		if (empty($var) || ctype_space($var) || $var == '')
			return false;

		else
		{
			require_once($sourcedir.'/Subs-Post.php');

			preparsecode($var);

			return $var;
		}
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
		global $smcFunc;

		if (is_array($var))
			return $var;

		if (get_magic_quotes_gpc())
			$var = stripslashes($var);

		if (is_numeric($var))
			$var = (int)trim($var);

		elseif (is_string($var))
			return $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($var), ENT_QUOTES);

		else
			$var = 'error_' . $var;

		return $var;
	}
}
