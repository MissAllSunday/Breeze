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

	function __construct()
	{
		global $user_info;

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
			'topic'
		);

		/* We kinda need all this stuff, dont' ask why, just nod your head... */
		$this->_settings = Breeze::settings();
		$this->_query = Breeze::query();
		$this->_tools = Breeze::tools();
		$this->_text = Breeze::text();
	}

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

	public function createBuddy($params)
	{
		loadLanguage(Breeze::$name);

		/* We need a quick query here */
		$tempQuery = $this->quickQuery('breeze_notifications');

		/* if the type is buddy then let's do a check to avoid duplicate entries */
		if (!empty($params) && in_array($params['type'], $this->_types))
		{
			/* Doing a quick query will be better than loading the entire notifications array */
			$tempQuery->params(
				array(
					'rows' => 'id',
					'where' => 'user = {int:user}',
					'whereAnd' => 'user_to = {int:user_to}',
				),
				array(
					'user' => !empty($params['user']) ? $params['user'] : $this->_currentUser,
					'user_to' => $params['user_to'],
				)
			);
			$tempQuery->getData('id');

			$return = $tempQuery->dataResult();

			/* Patience is a virtue, you obviously don't know that, huh? */
			if (!empty($return))
				fatal_lang_error('Breeze_buddyrequest_error_doublerequest', false);

			/* We are good to go */
			else
				$this->create($params);
		}

		else
			return false;
	}

	public function count()
	{
		return count($this->_query->getNotifications());
	}

	protected function getByUser($user)
	{
		/* Dont even bother... */
		if (empty($user))
			return false;

		return $this->_query->getNotificationByUser($user);
	}

	public function doStream($user)
	{
		global $context;

		/* Safety */
		if (empty($user))
			return false;

		/* Get all the notification for this user */
		$this->_all = $this->getByUser($user);

		/* Do this if there is actually something to show */
		if (!empty($this->_all))
		{
			/* Call the methods */
			foreach($this->_all as $all)
				if (in_array($all['type'], $this->_types))
				{
					/* load the user's link */
					if (empty($context['Breeze']['user_info'][$all['user']]))
						$this->_tools->loadUserInfo($all['user']);

					$call = 'do' . ucfirst($all['type']);
					$this->$call($all);
				}

			/* Show the notifications */
			if (!empty($this->_messages))
			{
				$context['insert_after_template'] .= '
				<script type="text/javascript"><!-- // --><![CDATA[
		$(document).ready(function()
		{
';

				foreach($this->_messages as $m)
					$context['insert_after_template'] .= 'noty({
		text: '. JavaScriptEscape($m) .',
		type: \'notification\',
		dismissQueue: true,
		layout: \'topRight\',
		closeWith: [\'button\'],
		buttons: [
			{addClass: \'button_submit\', text: breeze_noti_markasread, onClick: function($noty) {
				// make an ajax call here
				$noty.close();
				noty({text: breeze_noti_markasread_after, timeout: 1500, type: \'success\'});
			}
			},
			{addClass: \'button_submit\', text: breeze_noti_delete, onClick: function($noty) {
				// make an ajax call here
				$noty.close();
				noty({text: breeze_noti_delete_after, timeout: 1500, type: \'success\'});
			  }
			},
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

	protected function doBuddy($noti)
	{
		global $context;

		/* Extra check */
		if ($noti['user_to'] != $this->_currentUser)
			return false;

		$this->_tools->loadUserInfo($noti['user_to']);

		/* Fill out the messages property */
		$this->_messages[] = sprintf($this->_text->getText('buddy_messagerequest_message'), $context['Breeze']['user_info'][$noti['user']]['link']);
	}

	protected function doMention($noti)
	{
		global $context, $scripturl;

		/* Extra check */
		if ($noti['user_to'] != $this->_currentUser)
			return false;

		/* Yeah, we started with nothing! */
		$text = '';

		/* Lots of users to load */
		$this->_tools->loadUserInfo(array(
			$noti['content']['wall_owner'],
			$noti['content']['wall_poster'],
			$noti['user_to'],
		));

		/* Build the status link */
		$statusLink = $scripturl .'?action=profile;area=wallstatus;u='. $noti['content']['wall_owner'] .';bid='. $noti['content']['status_id'];

		/* Is this a mention on a comment? */
		if (isset($noti['comment_id']) && !empty($noti['comment_id']))
		{
			/* Is this the same user's wall? */
			if ($noti['content']['wall_owner'] == $noti['user_to'])
				$text = sprintf(
					$this->_text->getText('mention_message_own_wall_comment'),
					$statusLink,
					$context['Breeze']['user_info'][$noti['content']['wall_poster']]['link']
				);

			/* This is someone elses wall, go figure... */
			else
				$text = sprintf(
					$this->_text->getText('mention_message_comment'),
					$context['Breeze']['user_info'][$noti['content']['wall_poster']]['link'],
					$context['Breeze']['user_info'][$noti['content']['wall_owner']]['link'],
					$statusLink
				);
		}

		/* No? then this is a mention made on a status */
		else
		{
			/* Is this your own wall? */
			if ($noti['content']['wall_owner'] == $noti['user_to'])
				$text = sprintf(
					$this->_text->getText('mention_message_own_wall_status'),
					$statusLink,
					$context['Breeze']['user_info'][$noti['content']['wall_poster']]['link']
				);

			/* No? don't worry, you will get your precious notification anyway */
			elseif ($noti['content']['wall_owner'] != $noti['user_to'])
				$text = sprintf(
					$this->_text->getText('mention_message_comment'),
					$context['Breeze']['user_info'][$noti['content']['wall_poster']]['link'],
					$context['Breeze']['user_info'][$noti['content']['wall_owner']]['link'],
					$statusLink
				);
		}

		/* Create the message already */
		$this->_messages[] = $text;
	}

	protected function delete($id)
	{
		$this->query->deleteNotification($id);
	}

	protected function markAsRead($id)
	{
		$this->query->MarkAsReadNotification($id);
	}
}