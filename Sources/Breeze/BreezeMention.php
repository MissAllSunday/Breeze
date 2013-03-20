<?php

/**
 * BreezeMention
 *
 * The purpose of this file is to identify a mention in a string and convert that to a more easy to use format which will be used by the aprser class later, oh, and create the mention notification..
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <missallsunday@simplemachines.org>
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

class BreezeMention
{
	protected $_notification;
	protected $_settings;
	protected $_string;
	protected $_regex;
	protected $_query;
	protected $_searchNames = array();
	protected $_queryNames = array();

	function __construct($settings, $query, $notifications)
	{
		$this->_regex = '~@\(([\s\w,;-_\[\]\\\/\+\.\~\$\!]+), ([0-9]+)\)~u';
		$this->_notification = $notifications;
		$this->_settings = $settings;
		$this->_query = $query;
	}

	/*
	 * Gets the raw string, parses it and creates the notifications
	 *
	 * @see BreezeAjax class
	 * @access public
	 * @return void
	 */
	public function mention($noti_info = array())
	{
		global $user_info;

		// Search for all possible names
		if (preg_match_all($this->_regex, $this->_string, $matches, PREG_SET_ORDER))
			foreach ($matches as $m)
				$this->_queryNames[$m[2]] = $m;

		// No? nada? :(
		if (empty($this->_queryNames))
			return false;

		// We need an array and users won't be notified twice...
		$this->_queryNames = array_unique(is_array($this->_queryNames) ? $this->_queryNames : array($this->_queryNames));

		// You can't notify yourself
		if (isset($this->_queryNames[$user_info['id']]))
			unset($this->_queryNames[$user_info['id']]);

		// Sorry, theres gotta be a limit you know?
		$admin_mention_limit = $this->_settings->enable('admin_mention_limit') ? $this->_settings->getSetting('admin_mention_limit') : 10;

		// Chop the array off!
		if (!empty($admin_mention_limit) && count($this->_queryNames) >= $admin_mention_limit)
			$this->_queryNames = array_slice($this->_queryNames, 0, $admin_mention_limit);

		foreach ($this->_queryNames as $name)
		{
			// Append the mentioned user ID
			$noti_info['wall_mentioned'] = $name[2];

			// Notification here
			$this->_notification->create(array(
				'user' => $user_info['id'],
				'user_to' => $name[2],
				'type' => 'mention',
				'time' => time(),
				'read' => 0,
				'content' => $noti_info,
			));
		}
	}
}
