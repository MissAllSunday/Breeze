<?php

/**
 * BreezeMention
 *
 * The purpose of this file is to identify a mention in a string and convert that to a more easy to use format which will be used by the parser class later on, oh, and create the mention notification...
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
			// Append the mentioned user ID
			$noti_info['wall_mentioned'] = $name['id'];

			// Notification here
			$this->_notification->create(array(
				'sender' => $user_info['id'],
				'receiver' => $name['id'],
				'type' => 'mention',
				'time' => time(),
				'read' => 0,
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
