<?php

/**
 * BreezeNotifications
 *
 * The purpose of this file is to fetch all notifications for X user
 * @package Breeze mod
 * @version 1.0 Beta 3
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

class BreezeNotifications extends Breeze
{
	protected $_settings = array();
	protected $_params = array();
	protected $_user = 0;
	private $_query;
	protected $_returnArray = array();
	protected $_usersData = array();
	protected $_types = array();
	protected $_currentUser;
	protected $_messages = array();

	/**
	 * BreezeNotifications::__construct()
	 *
	 * @return
	 */
	function __construct()
	{
		global $user_info;

		/* Call the parent */
		parent::__construct();

		/* Current user */
		$this->_currentUser = $user_info['id'];

		$this->_types = array(
			'comment',
			'status',
			'like',
			'buddy',
			'mention',
			'reply',
			'topic');

		/* We kinda need all this stuff, dont' ask why, just nod your head... */
		$this->_settings = $this->settings();
		$this->_query = $this->query();
		$this->_tools = $this->tools();
		$this->_text = $this->text();
	}

	/**
	 * BreezeNotifications::create()
	 *
	 * @param mixed $params
	 * @return
	 */
	public function create($params)
	{
		/* We have to make sure, we just have to! */
		if (!empty($params) && in_array($params['type'], $this->_types))
		{
			/* Is there additional content? */
			if (!empty($params['content']))
				$params['content'] = json_encode($params['content']);

			else
				$params['content'] = '';

			$this->_query->insertNotification($params);
		}

		else
			return false;
	}

	/**
	 * BreezeNotifications::createBuddy()
	 *
	 * @param mixed $params
	 * @return
	 */
	public function createBuddy($params)
	{
		loadLanguage(Breeze::$name);

		/* if the type is buddy then let's do a check to avoid duplicate entries */
		if (!empty($params) && in_array($params['type'], $this->_types))
		{
			/* Doing a quick query will be better than loading the entire notifications array */
			$tempQuery = $this->query()->quickQuery(
				array(
					'table' => 'breeze_notifications',
					'rows' => 'id',
					'where' => 'user = {int:user}',
					'and' => 'user_to = {int:user_to}',
					'andTwo' => 'type = {string:type}',
				),
				array(
					'user' => !empty($params['user']) ? $params['user'] : $this->_currentUser,
					'user_to' => $params['user_to'],
					'type' => $params['type'],
				),
				'id', false
			);

			/* Patience is a virtue, you obviously don't know that, huh? */
			if (!empty($tempQuery))
				fatal_lang_error('Breeze_buddyrequest_error_doublerequest', false);

			/* We are good to go */
			else
				$this->create($params);
		}

		else
			return false;
	}

	/**
	 * BreezeNotifications::count()
	 *
	 * @return
	 */
	public function count()
	{
		return count($this->_query->getNotifications());
	}

	/**
	 * BreezeNotifications::getToUser()
	 *
	 * @param int $user
	 * @param bool $all
	 * @return array
	 */
	public function getToUser($user, $all = false)
	{
		/* Dont even bother... */
		if (empty($user))
			return false;

		$temp = $this->_query->getNotificationByUser($user);

		/* Send those who hasn't been viewed */
		if (!$all && !empty($temp))
			foreach ($temp as $k => $t)
			{
				if (!empty($t['viewed']))
					unset($temp[$k]);

				else
					$temp[$t['id']] = $t;
			}

		return $temp;
	}

	/**
	 * BreezeNotifications::getByUser()
	 *
	 * @param mixed $user
	 * @param bool $all
	 * @return
	 */
	public function getByUser($user, $all = false)
	{
		/* Dont even bother... */
		if (empty($user))
			return false;

		$temp = $this->_query->getNotificationByUserSender($user);

		/* Send those who hasn't been viewed */
		if (!$all && !empty($temp))
			foreach ($temp as $k => $t)
			{
				if (!empty($t['viewed']))
					unset($temp[$k]);

				else
					$temp[$t['id']] = $t;
			}

		return $temp;
	}

	/**
	 * BreezeNotifications::doStream()
	 *
	 * @param mixed $user
	 * @return
	 */
	public function doStream($user)
	{
		global $context;

		/* Safety */
		if (empty($user))
			return false;

		/* Get all the notification for this user */
		$this->_all = $this->getToUser($user);

		/* Do this if there is actually something to show */
		if (!empty($this->_all))
		{
			/* Call the methods */
			foreach ($this->_all as $single)
				if (in_array($single['type'], $this->_types))
				{
					$call = 'do' . ucfirst($single['type']);
					$this->$call($single);
				}

			/* Show the notifications */
			if (!empty($this->_messages))
			{
				/* Make sure its an array */
				$this->_messages = !is_array($this->_messages) ? array($this->_messages) : $this->_messages;

				/* @todo move this to breeze.js */
				$context['insert_after_template'] .= '
				<script type="text/javascript"><!-- // --><![CDATA[
		$(document).ready(function()
		{
';

				foreach ($this->_messages as $m)
					$context['insert_after_template'] .= '
	var noti_id = \'' . $m['id'] . '\';
	var user = \'' . $m['user'] . '\';
	noty({
		text: ' . JavaScriptEscape($m['message']) . ',
		type: \'notification\',
		dismissQueue: true,
		layout: \'topRight\',
		closeWith: [\'button\'],
		buttons: [{
				addClass: \'button_submit\', text: breeze_noti_markasread, onClick: function($noty) {
					// make an ajax call here
					jQuery.ajax(
					{
						type: \'POST\',
						url: smf_scripturl + \'?action=breezeajax;sa=notimarkasread\',
						data: ({content : noti_id, user : user}),
						cache: false,
						dataType: \'json\',
						success: function(html)
						{
							if(html.type == \'error\')
							{
								noty({text: breeze_error_message, timeout: 3500, type: \'error\'});
							}

							else if(html.type == \'ok\')
							{
								noty({text: breeze_noti_markasread_after, timeout: 3500, type: \'success\'});
							}
						},
						error: function (html)
						{
							noty({text: breeze_error_message, timeout: 3500, type: \'error\'});
						},
					});

					$noty.close();
				}
			},
			{addClass: \'button_submit\', text: breeze_noti_delete, onClick: function($noty) {
				// make an ajax call here
					jQuery.ajax(
					{
						type: \'POST\',
						url: smf_scripturl + \'?action=breezeajax;sa=notidelete\',
						data: ({content : noti_id, user : user}),
						cache: false,
						dataType: \'json\',
						success: function(html)
						{
							if(html.type == \'error\')
							{
								noty({text: html.data, timeout: 3500, type: \'error\'});
							}

							else if(html.type == \'deleted\')
							{
								noty({text: html.data, timeout: 3500, type: \'error\'});
							}

							else if(html.type == \'ok\')
							{
								noty({text: html.data, timeout: 3500, type: \'success\'});
							}
						},
						error: function (html)
						{
							noty({text: breeze_error_message, timeout: 3500, type: \'error\'});
						},
					});
				$noty.close();
			}},
			{addClass: \'button_submit\', text: breeze_noti_cancel, onClick: function($noty) {
				$noty.close();
			  }
			}
		  ]
	});';

				$context['insert_after_template'] .= '
		});
		// ]]></script>';
			}
		}
	}

	/**
	 * BreezeNotifications::doBuddy()
	 *
	 * @param mixed $noti
	 * @return
	 */
	protected function doBuddy($noti)
	{
		global $context;

		/* Extra check */
		if (empty($noti) || !is_array($noti) || $noti['user_to'] != $this->_currentUser)
			return false;

		$this->_messages[$noti['id']]['id'] = $noti['id'];
		$this->_messages[$noti['id']]['user'] = $noti['user_to'];

		/* Fill out the messages property */
		$this->_messages[$noti['id']]['message'] = sprintf($this->_text->getText('buddy_messagerequest_message'),
			$context['Breeze']['user_info'][$noti['user']]['link'], $noti['id']);
	}

	/**
	 * BreezeNotifications::doMention()
	 *
	 * @param mixed $noti
	 * @return
	 */
	protected function doMention($noti)
	{
		global $context, $scripturl;

		/* Extra check */
		if ($noti['user_to'] != $this->_currentUser)
			return false;

		/* Yeah, we started with nothing! */
		$text = '';

		/* Build the status link */
		$statusLink = $scripturl . '?action=profile;area=wallstatus;u=' . $noti['content']['wall_owner'] .
			';bid=' . $noti['content']['status_id'];

		/* Is this a mention on a comment? */
		if (isset($noti['comment_id']) && !empty($noti['comment_id']))
		{
			/* Is this the same user's wall? */
			if ($noti['content']['wall_owner'] == $noti['user_to'])
				$text = sprintf($this->_text->getText('mention_message_own_wall_comment'), $statusLink,
					$context['Breeze']['user_info'][$noti['content']['wall_poster']]['link'], $noti['id']);

			/* This is someone elses wall, go figure... */
			else
				$text = sprintf($this->_text->getText('mention_message_comment'), $context['Breeze']['user_info'][$noti['content']['wall_poster']]['link'],
					$context['Breeze']['user_info'][$noti['content']['wall_owner']]['link'], $statusLink,
					$noti['id']);
		}

		/* No? then this is a mention made on a status */
		else
		{
			/* Is this your own wall? */
			if ($noti['content']['wall_owner'] == $noti['user_to'])
				$text = sprintf($this->_text->getText('mention_message_own_wall_status'), $statusLink,
					$context['Breeze']['user_info'][$noti['content']['wall_poster']]['link'], $noti['id']);

			/* No? don't worry, you will get your precious notification anyway */
			elseif ($noti['content']['wall_owner'] != $noti['user_to'])
				$text = sprintf($this->_text->getText('mention_message_comment'), $context['Breeze']['user_info'][$noti['content']['wall_poster']]['link'],
					$context['Breeze']['user_info'][$noti['content']['wall_owner']]['link'], $statusLink,
					$noti['id']);
		}

		/* Create the message already */
		$this->_messages[$noti['id']] = array(
			'id' => $noti['id'],
			'user' => $noti['user_to'],
			'message' => $text);
	}

	/**
	 * BreezeNotifications::delete()
	 *
	 * @param mixed $id
	 * @return
	 */
	public function delete($id)
	{
		$this->_query->deleteNotification($id);
	}

	/**
	 * BreezeNotifications::markAsRead()
	 *
	 * @param mixed $id
	 * @return
	 */
	public function markAsRead($id)
	{
		$this->_query->markAsviewedNotification($id);
	}
}
