<?php

/**
 * BreezeNotifications
 *
 * The purpose of this file is to fetch all notifications for X user
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <suki@missallsunday.com>
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

class BreezeNotifications
{
	protected $_settings = array();
	protected $_params = array();
	protected $_user = 0;
	private $_query;
	protected $_returnArray = array();
	protected $_usersData = array();
	public $types = array();
	protected $_currentUser;
	protected $_messages = array();

	/**
	 * BreezeNotifications::__construct()
	 *
	 * @return
	 */
	function __construct($settings, $text, $tools, $query)
	{
		global $user_info;

		// Current user
		$this->_currentUser = $user_info['id'];

		// Don't include the log type here since its, well, a log, and we'll retrieve it somewhere else...
		$this->types = array(
			'comment',
			'status',
			'like',
			// 'buddy', todo refactors the buddy system
			'mention',
			'message',
			'topic',
		);

		// We kinda need all this stuff, dont' ask why, just nod your head...
		$this->_settings = $settings;
		$this->_query = $query;
		$this->_tools = $tools;
		$this->_text = $text;
	}

	public function getByReceiver($user)
	{
		return $this->_query->getNotificationByReceiver($user);
	}

	public function getBySender($user)
	{
		return $this->_query->getNotificationBySender($user);
	}

	/**
	 * BreezeNotifications::create()
	 *
	 * @param mixed $params
	 * @return
	 */
	public function create($params)
	{
		// Is there additional content?
		if (!empty($params['content']))
			$params['content'] = is_array($params['content']) ? json_encode($params['content']) : (is_object($params['content']) ? $params['content']() : $params['content']);

		else
			$params['content'] = '';

		$this->_query->insertNotification($params);
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

		// if the type is buddy then let's do a check to avoid duplicate entries
		if (!empty($params) && in_array($params['type'], $this->types))
		{
			// Doing a quick query will be better than loading the entire notifications array
			$tempQuery = $this->_query->quickQuery(
				array(
					'table' => 'breeze_notifications',
					'rows' => 'id',
					'where' => 'user = {int:user}',
					'and' => 'receiver = {int:receiver}',
					'andTwo' => 'type = {string:type}',
				),
				array(
					'user' => !empty($params['user']) ? $params['user'] : $this->_currentUser,
					'receiver' => $params['receiver'],
					'type' => $params['type'],
				),
				'id', false
			);

			// Patience is a virtue, you obviously don't know that, huh?
			if (!empty($tempQuery))
				fatal_lang_error('Breeze_buddyrequest_error_doublerequest', false);

			// We are good to go
			else
				$this->create($params);
		}

		else
			return false;
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

		// Safety
		if (empty($user))
			return false;

		// Get all the notification for this user
		$this->_all = $this->getByReceiver($user);

		// Load the users data
		$this->_tools->loadUserInfo($this->_all['users']);

		// Get the actual class methods
		$doMhetods = get_class_methods(__CLASS__);

		// If we aren't in the profile then we must call a function in a source file far far away...
		if (empty($context['member']['options']))
		{
			global $sourcedir;

			require_once($sourcedir . '/Profile-Modify.php');

			// Call and set $context['member']['options']
			loadThemeOptions($this->_currentUser);
		}

		// Do this if there is actually something to show
		if (!empty($this->_all['data']))
		{
			// Call the methods
			foreach ($this->_all['data'] as $single)
				if (in_array($single['type'], $this->types) && $this->_tools->isJson($single['content']))
				{
					// We're pretty sure there is a method for this noti and that content is a json string so...
					$single['content'] = json_decode($single['content'], true);

					$call = 'do' . ucfirst($single['type']);

					// Call the right method
					$this->$call($single);
				}

			// Show the notifications
			if (!empty($this->_messages))
			{
				// Make sure its an array
				$this->_messages = !is_array($this->_messages) ? array($this->_messages) : $this->_messages;

				// @todo move this to breeze.js
				$context['insert_after_template'] .= '
				<script type="text/javascript"><!-- // --><![CDATA[
		$(document).ready(function()
		{
';
				foreach ($this->_messages as $m)
					$context['insert_after_template'] .= '
	var noti_id_' . $m['id'] . ' = \'' . $m['id'] . '\';
	var user_' . $m['user'] . ' = \'' . $m['user'] . '\';
	noty({
		text: ' . JavaScriptEscape($m['message']) . ',
		type: \'notification\',
		dismissQueue: true,
		layout: \'topRight\',
		closeWith: [\'button\'],
		buttons: [{
				addClass: \'button_submit\', text: breeze_noti_markasread, onClick: function($noty) {
					jQuery.ajax(
					{
						type: \'POST\',
						url: smf_scripturl + \'?action=breezeajax;sa=notimark;js=1\',
						data: ({content : noti_id_' . $m['id'] . ', user : user_' . $m['user'] . '}),
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
					jQuery.ajax(
					{
						type: \'POST\',
						url: smf_scripturl + \'?action=breezeajax;sa=notidelete;js=1\',
						data: ({content : noti_id_' . $m['id'] . ', user : user_' . $m['user'] . '}),
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

				// A close all button
				$context['insert_after_template'] .=
		'noty({
		text: \''. $this->_text->getText('noti_closeAll') .'\',
		type: \'warning\',
		dismissQueue: true,
		layout: \'topRight\',
		closeWith: [\'click\'],
		callback: {
			afterClose: function() {
				jQuery.noty.closeAll();
			},
			'. (!empty($context['member']['options']['Breeze_clear_noti']) ?  'onShow: function() {window.setTimeout("jQuery.noty.closeAll()", '. $context['member']['options']['Breeze_clear_noti'] * 1000 .');},' : '') .'
		},
	});
';

				// Close the js call
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
	public function doBuddy($noti)
	{
		global $context;

		// Extra check
		if (empty($noti) || !is_array($noti) || $noti['receiver'] != $this->_currentUser)
			return false;

		// @todo let BreezeBuddy to handle all the logic here, you just need to take care of showing the actual message...

		$this->_messages[$noti['id']]['id'] = $noti['id'];
		$this->_messages[$noti['id']]['user'] = $noti['receiver'];
		$this->_messages[$noti['id']]['viewed'] = $noti['viewed'];

		// Fill out the messages property
		$this->_messages[$noti['id']]['message'] = sprintf($this->_text->getText('buddy_messagerequest_message'),
			$context['Breeze']['user_info'][$noti['user']]['link'], $noti['id']);
	}

	/**
	 * BreezeNotifications::doMention()
	 *
	 * @param mixed $noti
	 * @return
	 */
	public function doMention($noti)
	{
		global $context, $scripturl;

		// Extra check
		if ($noti['receiver'] != $this->_currentUser)
			return false;

		// Yeah, we started with nothing!
		$text = '';

		// Build the status link
		$statusLink = $scripturl . '?action=profile;area=wallstatus;u=' . $noti['content']['wall_owner'] .
			';bid=' . $noti['content']['status_id'];

		// Sometimes this data hasn't been loaded yet
		if (empty($context['Breeze']['user_info'][$noti['content']['wall_poster']]) || empty($context['Breeze']['user_info'][$noti['content']['wall_owner']]))
		$this->_tools->loadUserInfo(array($noti['content']['wall_poster'], $noti['content']['wall_owner']));

		// Is this a mention on a comment?
		if (isset($noti['comment_id']) && !empty($noti['comment_id']))
		{
			// Is this the same user's wall?
			if ($noti['content']['wall_owner'] == $noti['receiver'])
				$text = sprintf($this->_text->getText('mention_message_own_wall_comment'), $statusLink,
					$context['Breeze']['user_info'][$noti['content']['wall_poster']]['link'], $noti['id']);

			// This is someone elses wall, go figure...
			else
				$text = sprintf($this->_text->getText('mention_message_comment'), $context['Breeze']['user_info'][$noti['content']['wall_poster']]['link'],
					$context['Breeze']['user_info'][$noti['content']['wall_owner']]['link'], $statusLink,
					$noti['id']);
		}

		// No? then this is a mention made on a status
		else
		{
			// Is this your own wall?
			if ($noti['content']['wall_owner'] == $noti['receiver'])
				$text = sprintf($this->_text->getText('mention_message_own_wall_status'), $statusLink,
					$context['Breeze']['user_info'][$noti['content']['wall_poster']]['link'], $noti['id']);

			// No? don't worry, you will get your precious notification anyway
			elseif ($noti['content']['wall_owner'] != $noti['receiver'])
				$text = sprintf($this->_text->getText('mention_message_comment'), $context['Breeze']['user_info'][$noti['content']['wall_poster']]['link'], $context['Breeze']['user_info'][$noti['content']['wall_owner']]['link'], $statusLink, $noti['id']);
		}

		// Create the message already
		$this->_messages[$noti['id']] = array(
			'id' => $noti['id'],
			'user' => $noti['receiver'],
			'message' => $text,
			'viewed' => $noti['viewed']
		);
	}

	public function getMessages()
	{
		if (!empty($this->_messages))
			return $this->_messages;

		else
			return false;
	}

	public function getAll()
	{
		if (!empty($this->_all))
			return $this->_all;

		else
			return false;
	}
}
