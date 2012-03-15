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
		global $user_info, $scripturl, $context, $memberContext;

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
		$notification = new Breeze_Notifications();
		$settings = Breeze_Settings::getInstance();


		/* There's gotta be an user... */
		if ($sa->Validate('u') == false)
			fatal_lang_error('no_access', false);

		/* Problems in paradise? */
		if (in_array($sa->Raw('u'), $user_info['buddies']))
		{
			$user_info['buddies'] = array_diff($user_info['buddies'], array($sa->Raw('u')));

			/* Do the update */
			updateMemberData($user_info['id'], array('buddy_list' => implode(',', $user_info['buddies'])));

			/* Done here, let's redirect the user to the profile page */
			redirectexit('action=profile;u=' . $sa->Raw('u'));
		}

		/* Before anything else, let's ask the user shall we? */
		elseif ($user_info['id'] != $sa->Raw('u'))
		{
			/* Load the users link */
			$user_load = array(
				$user_info['id'],
				$sa->Raw('u')
			);

		/* Load all the members up. */
		loadMemberData($user_load, false, 'profile');

		/* Setup the context for each buddy. */
		$temp_users_load = array();
		foreach ($user_load as $buddy)
		{
			loadMemberContext($buddy);
			$temp_users_load[$buddy] = $memberContext[$buddy];
		}

			$params = array(
				'user' => $sa->Raw('u'),
				'type' => 'buddy',
				'time' => time(),
				'read' => 0,
				'content' => array(
					'message' => sprintf($settings->GetText('buddy_messagerequest_message'), $temp_users_load[$user_info['id']]['link']),
					'url' => $scripturl .'?action=profile;area=breezebuddies;u='. $sa->Raw('u'),
					'from_link' => $temp_users_load[$user_info['id']]['link'],
					'from_id' => $user_info['id']
				)
			);

			/* Notification here */
			$notification->Create($params);

			/* Show a nice message saying the user must approve the friendship request */
			redirectexit('action=breezebuddyrequest;u=' . $sa->Raw('u'));
		}

	}

	public function ShowBuddyRequests($user)
	{
		Breeze::Load(array(
			'Query'
		));

		$query = Breeze_Query::getInstance();

		/* Load all buddy request for this user */
		$temp = $query->GetNotificationByType('buddy');
		$temp2 = array();

		/* We only want the notifications for this user... */
		foreach($temp as $t)
			if ($t['user'] == $user)
				$temp2[$t['id']] = $t;

		/* Return the notifications */
		return $temp2;
	}
}