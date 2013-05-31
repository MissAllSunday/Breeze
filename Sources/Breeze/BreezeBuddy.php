<?php

/**
 * BreezeBuddy
 *
 * The purpose of this file is to replace the default buddy action in SMF with one that provides more functionality.
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica Gonz�lez <suki@missallsunday.com>
 * @copyright Copyright (c) 2013 Jessica Gonz�lez
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
 * Jessica Gonz�lez.
 * Portions created by the Initial Developer are Copyright (c) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeBuddy
{
	public function  __construct($settings, $query, $notifications, $text)
	{
		$this->notification = $notifications;
		$this->settings = $settings;
		$this->query = $query;
		$this->text = $text;
	}

	public function buddy()
	{
		global $user_info, $scripturl, $context;

		checkSession('get');

		isAllowedTo('profile_identity_own');
		is_not_guest();

		// We need all this stuff
		$sa = Breeze::sGlobals('get');

		/* @todo theres a lot of ifs here... better split all cases into small methods */

		// There's gotta be an user...
		if ($sa->validate('u') == false)
			fatal_lang_error('no_access', false);

		// Problems in paradise?
		if (in_array($sa->getValue('u'), $user_info['buddies']))
		{

			// Does the user decided to remove the request before the other part was able to take any action?
			$doesExists = $this->query->getSingleNoti(array(
				'user_to' => $sa->getValue('u'),
				'user' => $user_info['id'],
			), 'buddy');

			// Tell the user to either wait or confirm the deletion
			if (!empty($doesExists))
			{
				// Te user really wants to do this... so be it!
				if ($sa->validate('forced') == true)
				{
					$user_info['buddies'] = array_diff($user_info['buddies'], array($sa->getValue('u')));

					// Do the update
					updateMemberData($user_info['id'], array('buddy_list' => implode(',', $user_info['buddies'])));
				}

				// Create the message to show
				$context['Breeze']['buddy']['title'] = $text->getText('noti_buddy_message_'. $globals->getValue('message') .'_title');
				$context['Breeze']['buddy']['message'] = $text->getText('noti_buddy_message_'. $globals->getValue('message') .'_message');

				redirectexit('action=breezebuddyrequest;u=' . $sa->getValue('u') .';message=1');
			}

			// Friendship is over, at least from one direction
			else
			{
				$user_info['buddies'] = array_diff($user_info['buddies'], array($sa->getValue('u')));
				updateMemberData($user_info['id'], array('buddy_list' => implode(',', $user_info['buddies'])));
			}

			// Done here, let's redirect the user to the profile page
			redirectexit('action=profile;u=' . $sa->getValue('u'));
		}

		// Before anything else, let's ask the user shall we?
		elseif ($user_info['id'] != $sa->getValue('u'))
		{
			// Notification here
			$this->notification->createBuddy(
				array(
					'user' => $user_info['id'],
					'user_to' => $sa->getValue('u'),
					'type' => 'buddy',
					'time' => time(),
					'read' => 0,
				)
			);

			// Show a nice message saying the user must approve the friendship request
			redirectexit('action=breezebuddyrequest;u=' . $sa->getValue('u') .';message=2');
		}
	}

	public function showBuddyRequests($user)
	{
		// I don't have time for this...
		if (empty($user))
			return false;

		// Load all buddy request for this user
		return $this->query->getNotificationByType('buddy', $user);
	}
}
