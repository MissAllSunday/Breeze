<?php

/**
 * BreezeBuddy
 *
 * The purpose of this file is to replace the default buddy action in SMF with one that provides more functionality.
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

class BreezeBuddy
{
	public function  __construct(){}

	public static function Buddy()
	{
		global $user_info, $scripturl, $context;

		checkSession('get');

		isAllowedTo('profile_identity_own');
		is_not_guest();

		/* We need all this stuff */
		$sa = Breeze::sGlobals('get');
		$notification = Breeze::notifications();
		$settings = Breeze::settings();

		/* There's gotta be an user... */
		if ($sa->validate('u') == false)
			fatal_lang_error('no_access', false);

		/* Problems in paradise? */
		if (in_array($sa->getRaw('u'), $user_info['buddies']))
		{
			$user_info['buddies'] = array_diff($user_info['buddies'], array($sa->getRaw('u')));

			/* Do the update */
			updateMemberData($user_info['id'], array('buddy_list' => implode(',', $user_info['buddies'])));

			/* Done here, let's redirect the user to the profile page */
			redirectexit('action=profile;u=' . $sa->getRaw('u'));
		}

		/* Before anything else, let's ask the user shall we? */
		elseif ($user_info['id'] != $sa->getRaw('u'))
		{
			$params = array(
				'user' => $user_info['id'],
				'user_to' => $sa->getRaw('u'),
				'type' => 'buddy',
				'time' => time(),
				'read' => 0,
			);

			/* Notification here */
			$notification->createBuddy($params);

			/* Show a nice message saying the user must approve the friendship request */
			redirectexit('action=breezebuddyrequest;u=' . $sa->getRaw('u'));
		}
	}

	public function ShowBuddyRequests($user)
	{
		global $context;

		$query = Breeze::query();
		$text = Breeze::text();
		$tools = Breeze::tools();

		/* Load all buddy request for this user */
		$temp = $query->getNotificationByType('buddy');
		$temp2 = array();

		/* We only want the notifications for this user... */
		foreach($temp as $t)
			if ($t['user_to'] == $user)
			{
				$temp2[$t['id']] = $t;

				/* load the user's link */
				if (!isset($context['Breeze']['user_info'][$t['user']]))
					$tools->loadUserInfo($t['user']);

				/* build the message */
				$temp2[$t['id']]['content']['message'] = sprintf ($text->getText('buddy_messagerequest_message'), $context['Breeze']['user_info'][$t['user']]['link']);
			}

		/* Return the notifications */
		return $temp2;
	}
}