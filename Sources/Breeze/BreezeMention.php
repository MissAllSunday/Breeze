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
	 * @access protected
	 * @return string the formatted string
	 */
	public function mention($noti_info = array())
	{
		global $user_info;

		// You can't notify yourself
		if (array_key_exists($user_info['id'], $this->_searchNames))
			unset($this->_searchNames[$user_info['id']]);

		foreach ($this->_searchNames as $name)
		{
			// Append the mentioned user ID
			$noti_info['wall_mentioned'] = $name['id_member'];

			// Notification here
			$this->_notification->create(array(
				'user' => $user_info['id'],
				'user_to' => $name['id_member'],
				'type' => 'mention',
				'time' => time(),
				'read' => 0,
				'content' => $noti_info,
			));
		}
	}
}
