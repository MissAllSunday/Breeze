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

		// We are gonna need some alerts language strings...
		$this->_app['tools']->loadLanguage('alerts');
	}

	public function get($users, $max, $start = 0, $limit = 10)
	{
		if (empty($user) || empty($max))
			return array();

		$this->_users = (array) $users;

		$this->_data = $this->_app['query']->getLogs($this->_users, $max, $start, $limit);

		// Parse the raw data.
		$this->call();

		// Return the formatted data.
		return $this->_data;
	}

	protected function call()
	{
		global $memberContext;

		// Kinda need this...
		if (empty($this->_data) || !is_array($this->_data))
			return;

		// The users to load.
		$toLoad = array();

		// Get the users before anything gets parsed.
		foreach ($this->_data as $idUser => $data)
				$toLoad = array_merge($toLoad, $a['extra']['toLoad']);

		if (!empty($toLoad))
			$this->_app['tools']->loadUserInfo($toLoad, false);

		// Pass people's data.
		$this->_usersData = $memberContext;

		// A few foreaches LOL
		foreach ($this->_data as $idUser => $data)
			$this->_data[$idUser][$data['id_alert']] = $this->$data['content_type']($data);
	}

	public function parser($text, $replacements = array())
	{
		if (empty($text) || empty($replacements) || !is_array($replacements))
			return false;

		// Split the replacements up into two arrays, for use with str_replace.
		$find = array();
		$replace = array();

		foreach ($replacements as $f => $r)
		{
			$find[] = '{' . $f . '}';
			$replace[] = $r;
		}

		// Do the variable replacements.
		return str_replace($find, $replace, $text);
	}

	public function cover()
	{

	}
}
