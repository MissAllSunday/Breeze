<?php

/**
 * Breeze_Buddy
 *
 * The purpose of this file is to replace the default buddy action in SMF with one that provides more functionality.
 * @package Breeze mod
 * @version 1.0 Beta 1
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

class Breeze_Buddy
{
	public function  __construct(){}

	public static function Buddy()
	{
		global $user_info, $scripturl;

		checkSession('get');

		isAllowedTo('profile_identity_own');
		is_not_guest();

		Breeze::Load(array(
			'Settings',
			'Globals',
			'Notifications',
			'UserInfo',
			'Query'
		));

		/* We need all this stuff */
		$sa = new Breeze_Globals('get');
		$notification = new Breeze_Notification();
		$setings = Breeze_Settings::getInstance();


		/* There's gotta be an user... */
		if ($sa->Validate('u') == false)
			fatal_lang_error('no_access', false);

		/* Problems in paradise? */
		if (in_array($sa->Raw('u'), $user_info['buddies']))
		{
			$user_info['buddies'] = array_diff($user_info['buddies'], array($sa->Raw('u')));

			/* Do the update */
			updateMemberData($user_info['id'], array('buddy_list' => implode(',', $user_info['buddies'])));
		}

		/* Before anything else, let's ask the user shall we? */
		elseif ($user_info['id'] != $sa->Raw('u'))
		{
			/* Load the users link */
			$user_asking = Breeze_UserInfo::Profile($user_info['id']);
			$user_receiving = Breeze_UserInfo::Profile($sa->Raw('u'));

			$params = array(
				'user' => $sa->Raw('u'),
				'time' => time(),
				'read' => 0,
				'content' => array(
					'title' => $settings->GetText('noti_buddy_title'),
					'message' => sprintf($settings->GetText('noti_buddy_message'), $context['Breeze']['user_info']['link'][$user_info['id']]),
					'url' => $scripturl .'?action=profile;area=breezebuddies;u='. $sa->Raw('u')
				)
			);

			/* Notification here */
			$notification->Create('buddy', $params);

			$user_info['buddies'][] = (int) $sa->Raw('u');

			/* Show a nice message saying the user must approve the friendship request */
			redirectexit('action=profile;area=breezebuddies;u=' . $sa->Raw('u'));
		}

	}

	public function ShowBuddyRequests($user)
	{
		$query = Breeze_Query::getInstance();

		/* Load all buddy request for this user */
		$query->GetNotificationByType($user);

		/* We only want the notification for this user... */
		
		
		/* Return the notifications */


	}
}