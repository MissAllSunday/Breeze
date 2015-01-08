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
	public $alerts = array('cover', 'mood', 'like', 'status', 'comment');

	function __construct($app)
	{
		$this->_app = $app;

		// We are gonna need some alerts language strings...
		$this->_app['tools']->loadLanguage('alerts');
	}

	public function get($users, $maxIndex, $start)
	{
		if (empty($users))
			return array();

		$this->_users = (array) $users;
		$this->_logCount =  $this->_app['query']->logCount($this->_users);

		$this->_data = $this->_app['query']->getLog($this->_users, $maxIndex, $start);

		// Parse the raw data.
		$this->call();

		// Return the formatted data.
		return array(
			'count' => $this->_logCount,
			'data' => $this->_data,
		);
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
				$toLoad = array_merge($toLoad, $data['extra']['toLoad']);

		if (!empty($toLoad))
			$this->_app['tools']->loadUserInfo($toLoad, false);

		// Pass people's data.
		$this->_usersData = $memberContext;

		// A few foreaches LOL
		foreach ($this->_data as $id => $data)
		{
			// Get the right gender stuff.
			$data['gender'] = !empty($this->_usersData[$data['member']]['options']['cust_gender']) ? $this->_usersData[$data['member']]['options']['cust_gender'] : 'None';

			$data['gender_possessive'] = $this->_app['tools']->text('alert_gender_possessive_'. $data['gender']) ? $this->_app['tools']->text('alert_gender_possessive_'. $data['gender']) : $this->_app['tools']->text('alert_gender_possessive_None');

			// Make sure we have a valid method for this and valid data too!
			if (method_exists($this, $data['content_type']) && !empty($data['extra']) && is_array($data['extra']))
				$this->_data[$id]['text'] = $this->$data['content_type']($data);

			// Add an empty text string.
			else
				$this->_data[$id]['text'] = '';
		}
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

	public function mood($data)
	{
		// Get the mood.
		$data['extra']['moodHistory'] = @unserialize($data['extra']['moodHistory']);
		$mood = !empty($data['extra']['moodHistory']['id']) ? $this->_app['query']->getMoodByID($data['extra']['moodHistory']['id'], true) : array();

		// Return the formatted string.
		return $this->parser($this->_app['tools']->text('alert_mood'), array(
			'poster' => $this->_usersData[$data['member']]['link'],
			'gender_possessive' => $data['gender_possessive'],
			'image' => !empty($mood) && !empty($mood['image_html']) ? $mood['image_html'] : '',
		));
	}

	public function cover($data)
	{
		// Gotta know if the image still exists.
		$filename = !empty($data['extra']['image']) ? $data['extra']['image'] : '';
		$file =  true;

		if (!empty($filename))
		{
			$file_headers = @get_headers($filename);

			if(empty($file_headers) || $file_headers[0] == 'HTTP/1.0 404 Not Found' || $file_headers[0] == 'HTTP/1.0 302 Found')
				$file = false;
		}

		else
			$file = false;

		return $this->parser($this->_app['tools']->text('alert_cover'), array(
			'poster' => $this->_usersData[$data['member']]['link'],
			'gender_possessive' => $data['gender_possessive'],
			'image' => $file ? ('<img src="'. $data['extra']['image'] .'" />') : '',
		));
	}

	public function status($data)
	{
		return $this->parser($this->_app['tools']->text($data['extra']['buddy_text']), array(
			'href' => $this->_app['tools']->scriptUrl . '?action=wall;sa=single;u=' . $data['wall_owner'] .
			';bid=' . $data['content_id'],
			'poster' => $this->_usersData[$data['poster']]['link'],
			'wall_owner' => $data['wall_owner'],
		));
	}

	public function comment($data)
	{

	}

	public function like($data)
	{
		return $this->parser($this->_app['tools']->text($data['extra']['buddy_text']), array(
			'href' => $this->_app['tools']->scriptUrl . '?action=wall;sa=single;u=' . $data['extra']['wall_owner'] .';bid=' . $data['extra']['status_id'] .';cid=' . $data['content_id'] .'#comment_id_' . $data['content_id'],
			'poster' => $this->_usersData[$data['extra']['poster']]['link'],
			'status_poster' => $this->_usersData[$data['extra']['status_owner']]['link'],
			'wall_owner' => $this->_usersData[$data['extra']['wall_owner']]['link'],
		));
	}
}
