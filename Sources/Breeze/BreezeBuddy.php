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
		global $user_info, $scripturl, $context, $memberContext;

		checkSession('get');

		isAllowedTo('profile_identity_own');
		is_not_guest();

		/* We need all this stuff */
		$sa = new BreezeGlobals('get');
		$notification = new BreezeNotifications();
		$settings = BreezeSettings::getInstance();

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
			$temp_users_load = BreezeSubs::LoadUserInfo($user_load);

			$params = array(
				'user' => $sa->Raw('u'),
				'type' => 'buddy',
				'time' => time(),
				'read' => 0,
				'content' => array(
					'message' => sprintf($settings->GetText('buddy_messagerequest_message'), $temp_users_load[$user_info['id']]['link']),
					'url' => $scripturl .'?action=profile;area=breezebuddies;u='. $sa->Raw('u'),
					'from_link' => $temp_users_load[$user_info['id']]['link'],
					'from_id' => $user_info['id'],
					'from_buddies' => $user_info['buddies']
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
		$query = BreezeQuery::getInstance();

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