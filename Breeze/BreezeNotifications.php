<?php

/**
 * BreezeNotifications
 *
 * The purpose of this file is to fetch all notifications for X user
 * @package Breeze mod
 * @version 1.0 Beta 2
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2012, Jessica González
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
	die('Hacking attempt...');

class BreezeNotifications
{
	protected $_types = array();
	protected $_params = array();
	private $_user = 0;
	private $_settings = '';
	private $_query = '';
	private $_returnArray = array();

	function __construct()
	{
		$this->_types = array(
			'comment',
			'status',
			'like',
			'buddy',
			'mention'
		);

		Breeze::loadFile(array(
			'Settings',
			'Query',
		));

		/* We kinda need all this stuff, dont' ask why, just nod your head... */
		$this->_settings = BreezeSettings::getInstance();
		$this->_query = BreezeQuery::getInstance();
	}

	public function create($params)
	{
		global $user_info;

		/* Set this as false by default */
		$double_request = false;

		/* if the type is buddy then let's do a check to avoid duplicate entries */
		if (!empty($params) && in_array($params['type'], $this->_types))
		{
			/* Load all the Notifications */
			$temp = $this->_query->getnotifications();

			if (!empty($temp))
				foreach ($temp as $t)
					if ($t['user'] == $params['user'] && $t['content']->from_id == $user_info['id'] && $t['type'] != 'mention')
						$double_request = true;
		}

		if ($double_request)
			fatal_lang_error('BreezeMod_buddyrequest_error_doublerequest', false);

		elseif (!empty($params) && in_array($params['type'], $this->_types) && !$double_request)
		{
			$this->_params = $params;
			$this->_query->InsertNotification($this->_params);
		}

		else
			return false;
	}

	public function count()
	{
		return count($this->_query->getnotifications());
	}

	protected function getByUser($user)
	{
		/* Dont even bother... */
		if (empty($user))
			return;

		$user = (int) $user;

		return $this->_query->getNotificationByUser($user);
	}

	public function stream($user)
	{
		return $this->getByUser($user);
	}

	protected function delete($id)
	{
		$this->_query->deleteNotification($id);
	}

	protected function markAsRead($id)
	{
		$this->_query->markAsReadNotification($id);
	}
}