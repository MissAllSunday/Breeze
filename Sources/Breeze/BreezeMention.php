<?php

/**
 * BreezeMention
 *
 * @package Breeze mod
 * @version 1.0.15
 * @author  Michel Mendiola <suki@missallsunday.com>
 * @copyright Copyright (c) 2011 - 2021 Michel Mendiola
 * @license //www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeMention
{
	protected $_notification;
	protected $_string = '';
	protected $_regex = array();
	protected $_query;
	protected $_tools;
	protected $_searchNames = array();
	protected $_queryNames = array();

	function __construct($tools, $query, $notifications)
	{
		$this->_notification = $notifications;
		$this->_query = $query;
		$this->_tools = $tools;
	}

	/*
	 * Gets the raw string, parses it and sets a new property with users data
	 *
	 * @see BreezeAjax class
	 * @access public
	 * @return string
	 */
	public function preMention($string, $mentions = array())
	{
		$this->_string = $string;

		if (empty($mentions))
			return $this->_string;

		// Search for all possible names
		$this->_queryNames = $mentions;

		// We need to replace all the @username with something that can be parsed
		foreach ($this->_queryNames as $name)
			$this->_string = str_replace('@'. $name['name'], '('. $name['name'] .', '. $name['id'] .')', $this->_string);

		return $this->_string;
	}

	/*
	 * Creates a notification based on external params
	 *
	 * @see BreezeAjax class
	 * @access public
	 * @return void
	 */
	public function mention($noti_info = array(), $type = array())
	{
		global $user_info;

		if (empty($this->_queryNames))
			return false;

		// You can't notify yourself
		if (isset($this->_queryNames[$user_info['id']]))
			unset($this->_queryNames[$user_info['id']]);

		// Sorry, theres gotta be a limit you know?
		$admin_mention_limit = $this->_tools->enable('mention_limit') ? $this->_tools->setting('mention_limit') : 10;

		// Chop the array off!
		if (!empty($admin_mention_limit) && count($this->_queryNames) >= $admin_mention_limit)
			$this->_queryNames = array_slice($this->_queryNames, 0, $admin_mention_limit);

		foreach ($this->_queryNames as $name)
		{
			// So, we need this user specific settings.
			$userSettings = $this->_query->getUserSettings($name['id']);

			// Append the mentioned user ID
			$noti_info['wall_mentioned'] = $name['id'];

			// Does this user wants to be notified?
			if (!empty($userSettings['noti_on_mention']))
				$this->_notification->create(array(
					'sender' => $user_info['id'],
					'receiver' => $name['id'],
					'type' => 'mention',
					'time' => time(),
					'viewed' => 0,
					'content' => $noti_info,
					'type_id' => !empty($type) && !empty($type['id']) ? $type['id'] : 0,
					'second_type' => !empty($type) && !empty($type['name']) ? $type['name'] : '',
				));
		}
	}

	public function getString()
	{
		return $this->_string;
	}

	public function getQueryNames()
	{
		return $this->_queryNames;
	}
}
