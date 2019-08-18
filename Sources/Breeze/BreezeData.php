<?php

declare(strict_types=1);

/**
 * BreezeData
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011 - 2017, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

namespace Breeze;

if (!defined('SMF'))
	die('No direct access...');

class BreezeData
{
	protected $_request;

    /**
     * BreezeData::__construct()
     *
     * @param bool $type
     */
	public function __construct($type = false)
	{
		if (!empty($type))
		{
			$types = ['request' => $_REQUEST, 'get' => $_GET, 'post' => $_POST, 'session' => $_SESSION];

			$this->_request = (empty($type) || !isset($types[$type])) ? $_REQUEST : $types[$type];

			unset($types);
		}

		else
			$this->_request = $_REQUEST;
	}

    /**
     * BreezeData::get()
     *
     * @return array|bool|int|mixed|string
     */
	public function get($value)
	{
		if ($this->validate($value))
			return $this->sanitize($this->_request[$value]);

		
			return false;
	}

    /**
     * BreezeData::getRaw()
     *
     * @return bool
     */
	public function getRaw($value)
	{
		if (isset($this->_request[$value]))
			return $this->_request[$value];

		
			return false;
	}

	public function getAll()
	{
		return $this->_request;
	}

    /**
     * BreezeData::validate()
     *
     * @return bool
     */
	public function validate($var)
	{
		return (isset($this->_request[$var]));
	}

    /**
     * BreezeData::validateBody()
     *
     * @return bool|mixed
     */
	public function validateBody($var)
	{
		global $sourcedir;

		// You cannot post just spaces
		if (empty($var) || ctype_space($var) || $var == '')
			return false;

		
		
			require_once($sourcedir . '/Subs-Post.php');

			preparsecode($var);

			return $var;
		
	}

    /**
     * BreezeData::unsetVar()
     *
     * @return void
     */
	public function unsetVar($var)
	{
		unset($this->_request[$var]);
	}

    /**
     * BreezeData::sanitize()
     *
     * @return array|bool|int|mixed|string
     */
	public function sanitize($var)
	{
		global $smcFunc;

		if (is_array($var))
		{
			foreach ($var as $k => $v)
				$var[$k] = $this->sanitize($v);

			return $var;
		}

		
		
			$var = (string) $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($var), ENT_QUOTES);

			if (ctype_digit($var))
				$var = (int) $var;

			if (empty($var))
				$var = false;
		

		return $var;
	}

	public function normalizeString($string = '')
	{
		global $context, $smcFunc;

		if (empty($string))
			return '';

		$string = $smcFunc['htmlspecialchars']($string, ENT_QUOTES, $context['character_set']);
		$string = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', $string);
		$string = html_entity_decode($string, ENT_QUOTES, $context['character_set']);
		$string = preg_replace(['~[^0-9a-z]~i', '~[ -]+~'], ' ', $string);

		return trim($string, ' -');
	}
}
