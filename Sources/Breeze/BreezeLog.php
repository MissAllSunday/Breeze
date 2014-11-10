<?php

/**
 * BreezeLog
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeLog
{
	protected $_users = array();
	protected $_data = array();
	protected $_app;

	function __construct($app)
	{
		$this->_app = $app;
	}

	public function get($users, $max, $start = 0, $limit = 10)
	{
		if (empty($user) || empty($max))
			return array();

		$this->_users = (array) $users;

		$this->_raw = $this->_app['query']->getLogs($this->_users, $max, $start, $limit);

		// Parse the raw data.
		$this->call();

		// Return the formatted data.
		return $this->_data;
	}

	protected function call()
	{
		global $memberContext;

		// Kinda need this...
		if (empty($this->_raw) || !is_array($this->_raw))
			return;

		// The users to load.
		$toLoad = array();

		// Get the users before anything gets parsed.
		foreach ($this->_raw as $idUser => $data)
				$toLoad = array_merge($toLoad, $a['extra']['toLoad']);

		if (!empty($toLoad))
			$this->_app['tools']->loadUserInfo($toLoad, false);

		// Pass people's data.
		$this->_usersData = $memberContext;

		// What type are we gonna handle? oh boy there are a lot!
		foreach ($this->_raw as $idUser => $data)
			if(method_exists($this, $data['content_type']))
				$this->_data[$idUser][$data['id_alert']]['text'] = $this->$data['content_type']($data['id_alert']);
	}
}
