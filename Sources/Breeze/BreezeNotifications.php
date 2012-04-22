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
	protected $types = array();
	protected $params = array();
	private $user = 0;
	private $settings = '';
	private $query = '';
	private $ReturnArray = array();
	private $usersData = array();

	function __construct()
	{
		$this->types = array(
			'comment',
			'status',
			'like',
			'buddy',
			'mention'
		);

		/* We kinda need all this stuff, dont' ask why, just nod your head... */
		$this->settings = BreezeSettings::getInstance();
		$this->query = BreezeQuery::getInstance();
	}

	public function Create($params)
	{
		global $user_info;

		/* Set this as false by default */
		$double_request = false;

		/* if the type is buddy then let's do a check to avoid duplicate entries */
		if (!empty($params) && in_array($params['type'], $this->types))
		{
			/* Load all the Notifications */
			$temp = $this->query->GetNotifications();

			if (!empty($temp))
				foreach ($temp as $t)
					if ($t['user'] == $params['user'] && $t['content']->from_id == $user_info['id'] && $t['type'] != 'mention')
						$double_request = true;
		}

		if ($double_request)
			fatal_lang_error('BreezeMod_buddyrequest_error_doublerequest', false);

		elseif (!empty($params) && in_array($params['type'], $this->types) && !$double_request)
		{
			$this->params = $params;

			/* Convert to a json string */
			$this->params['content'] = json_encode($this->params['content']);

			$this->query->InsertNotification($this->params);
		}

		else
			return false;
	}

	public function Count()
	{
		return count($this->query->GetNotifications());
	}

	protected function GetByUser($user)
	{
		/* Dont even bother... */
		if (empty($user))
			return;

		$user = (int) $user;

		return $this->query->GetNotificationByUser($user);
	}

	public function doStream($user)
	{
		global $context;

		$this->all = $this->GetByUser($user);

		/* Load users data */
		foreach ($this->all['content'] as $lu => $v)
			if ()
			$this->usersData[$v] = BreezeSubs::LoadUserInfo($v, true);

		$context['insert_after_template'] .= '
		<script type="text/javascript"><!-- // --><![CDATA[
$(document).ready(function()
{';

		/* Check for the type and act in accordance */
		foreach($this->all as $all)
			if (in_array($all['type'], $this->types))
			{
				$call = 'do' . ucfirst($this->types[$all['type']]);
				$context['insert_after_template'] .= $this->$call($all) == false ? '' : $this->$call($all);
			}

		$context['insert_after_template'] .= '
});

// ]]></script>';
	}

	protected function doComments($noti)
	{
		global $user_info;

		if ($noti['content']['user_who_commented'] == $user_info['id'])
			return false;

		if ($noti['content']['user_who_created_the_status'] == $user_info['id'])
			$message = '$.sticky(\''. JavaScriptEscape($s['content']->message) .'\');';

		return $message;
	}

	protected function Delete($id)
	{
		$this->query->DeleteNotification($id);
	}

	protected function MarkAsRead($id)
	{
		$this->query->MarkAsReadNotification($id);
	}
}