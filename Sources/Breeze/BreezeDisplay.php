<?php

/**
 * BreezeDisplay
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica GonzÃ¡lez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeDisplay
{
	protected $_app;

	function __construct($app)
	{
		$this->_app = $app;
	}

	public function HTML($params, $type, $single = false, $usersToLoad = false)
	{
		global $context;

		if (empty($params) || empty($type))
			return false;

		$return = array();
		$users = array();
		$call = 'breeze_'. $type;

		// Functions template
		loadtemplate(Breeze::$name .'Functions');

		if ($single)
			$params['time'] = $this->_app['tools']->timeElapsed($params['time']);

		// Let us work with an array
		$params = $single ? array($params) : $params;

		// If there is something to load, load it then!
		if ($usersToLoad)
			$this->_app['tools']->loadUserInfo($usersToLoad);

		// Call the template with return param as true
		$return = $call($params, true);

		// If single is true, return the first (and only) item in the array
		return $return;
	}
}
