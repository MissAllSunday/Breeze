<?php

/**
 * BreezeUser
 *
 * The purpose of this file is To show the user wall and provide a settings page
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

	/* A bunch of wrapper functions so static methods can be callable with a string by SMF */
	function Breeze_Wrapper_Wall(){BreezeUser::wall();}
	function Breeze_Wrapper_Settings($memID){BreezeUser::settings($memID);}
	function Breeze_Wrapper_BuddyRequest(){BreezeUser::buddyRequest();}
	function Breeze_Wrapper_BuddyMessageSend(){BreezeUser::buddyMessageSend();}
	function Breeze_Wrapper_Notifications(){BreezeUser::notifications();}
	function Breeze_Wrapper_Single(){BreezeUser::single();}

class BreezeUser
{
	public function  __construct(){}

	public static function wall()
	{
		global $txt, $scripturl, $context, $memberContext, $modSettings,  $user_info;

		loadtemplate(Breeze::$name);

		/* We kinda need all this stuff, dont' ask why, just nod your head... */
		$settings = Breeze::settings();
		$query = Breeze::query();
		$tools = Breeze::tools();
		$globals = Breeze::sGlobals('get');

		/* Default values */
		$status = array();
		$users_to_load = array();

		/* Another page already checked the permissions and if the mod is enable, but better be safe... */
		if (!$settings->enable('admin_settings_enable'))
			redirectexit();

		/* Does the user even enable this? */
		if (empty($context['member']['options']['Breeze_enable_wall']))
			redirectexit('action=profile;area=static;u='.$context['member']['id']);

		/* This user cannot see his/her own profile and cannot see any profile either */
		if (!allowedTo('profile_view_own') && !allowedTo('profile_view_any'))
			redirectexit('action=profile;area=static;u='.$context['member']['id']);

		/* This user cannot see his/her own profile and it's viewing his/her own profile */
		if (!allowedTo('profile_view_own') && $user_info['id'] == $context['member']['id'])
			redirectexit('action=profile;area=static;u='.$context['member']['id']);

		/* This user cannot see any profile and it's  viewing someone else's wall */
		if (!allowedTo('profile_view_any') && $user_info['id'] != $context['member']['id'])
			redirectexit('action=profile;area=static;u='.$context['member']['id']);

		/* Get this user's ignore list */
		$context['member']['ignore_list'] = array();

		$temp_ignore_list = $query->getUserSetting($context['member']['id'], 'pm_ignore_list');

		if (!empty($temp_ignore_list))
		$context['member']['ignore_list'] = explode(',', $temp_ignore_list);

		/* I'm sorry, you aren't allowed in here, but here's a nice static page :) */
		if (in_array($user_info['id'], $context['member']['ignore_list']) && !empty($context['member']['options']['Breeze_kick_ignored']))
			redirectexit('action=profile;area=static;u='.$context['member']['id']);

		/* display all the JavaScript bits */
		$tools->headers();

		/* Set all the page stuff */
		$context['sub_template'] = 'user_wall';
		$context += array(
			'page_title' => sprintf($txt['profile_of_username'], $context['member']['name']),
			'can_send_pm' => allowedTo('pm_send'),
			'can_have_buddy' => allowedTo('profile_identity_own') && !empty($modSettings['enable_buddylist']),
			'can_issue_warning' => in_array('w', $context['admin_features']) && allowedTo('issue_warning') && $modSettings['warning_settings'][0] == 1,
		);
		$context['user']['is_owner'] = $context['member']['id'] == $user_info['id'];
		$context['canonical_url'] = $scripturl . '?action=profile;u=' . $context['member']['id'];
		$context['member']['status'] = array();

		/* Load all the status */
		$status = $query->getStatusByProfile($context['member']['id']);

		/* Load the users data */
		if (!empty($status))
		{
			foreach($status as $s)
			{
				$usersArray[] = $s['owner_id'];
				$usersArray[] = $s['poster_id'];

				if (!empty($s['comments']))
					foreach($s['comments'] as $c)
						$usersArray[] = $c['poster_id'];
			}

			$tools->loadUserInfo(array_unique($usersArray));
		}

		/* Getting the current page. */
		$page = $globals->validate('page') == true ? $globals->getRaw('page') : 1;

		/* Applying pagination. */
		$pagination = new BreezePagination($status, $page, '?action=profile;page=', '', 15, 5);
		$pagination->PaginationArray();
		$pagtrue = $pagination->PagTrue();

		/* Send the array to the template if there is pagination */
		if ($pagtrue)
		{
			$context['member']['status'] = $pagination->OutputArray();
			$context['Breeze']['pagination']['panel'] = $pagination->OutputPanel();
		}

		/* If not, then let's use the default array */
		else
		{
			$context['member']['status'] = $status;
			$context['Breeze']['pagination']['panel'] = '';
		}

		/* The visitor's permissions */
		$context['Breeze']['visitor']['post_status'] = allowedTo('breeze_postStatus') || $context['user']['is_owner'];
		$context['Breeze']['visitor']['post_comment'] = allowedTo('breeze_postComments') || $context['user']['is_owner'];
		$context['Breeze']['visitor']['delete_status_comments'] = allowedTo('breeze_deleteStatus') || $context['user']['is_owner'];
	}

	/* Shows a form for users to set up their wall as needed. */
	public static function settings($memID)
	{
		global $context;

		Breeze::load('Profile-Modify');
		loadtemplate(Breeze::$name);

		loadThemeOptions($memID);
		if (allowedTo(array('profile_extra_own', 'profile_extra_any')))
			loadCustomFields($memID, 'theme');

		$context['Breeze']['text'] = Breeze::text();
		$context['sub_template'] = 'member_options';
		$context['page_desc'] = $context['Breeze']['text']->getText('user_settings_enable_wall');

		/* Create the form */
		$form = new BreezeForm();

		$form->addCheckBox(
			'Breeze_enable_wall',
			'enable_wall',
			!empty($context['member']['options']['Breeze_enable_wall']) ? true : false
		);

		$form->addText(
			'Breeze_pagination_number',
			'pagination_number',
			!empty($context['member']['options']['Breeze_pagination_number']) ? $context['member']['options']['Breeze_pagination_number'] : 0,
			3,3
		);

		$form->addCheckBox(
			'Breeze_infinite_scroll',
			'infinite_scroll',
			!empty($context['member']['options']['Breeze_infinite_scroll']) ? true : false
		);

		$form->addCheckBox(
			'Breeze_kick_ignored',
			'kick_ignored',
			!empty($context['member']['options']['Breeze_kick_ignored']) ? true : false
		);

		$form->addCheckBox(
			'Breeze_enable_visits_module',
			'enable_visits_module',
			!empty($context['member']['options']['Breeze_enable_visits_module']) ? true : false
		);

		$form->addSelect(
			'Breeze_visits_timeframe',
			'visits_module_timeframe',
			array(
				'Hour' => array(
					'visits_module_timeframe_hour',
					!empty($context['member']['options']['Breeze_visits_timeframe']) && $context['member']['options']['Breeze_visits_timeframe'] == 'Hour' ? 'selected' : ''
				),
				'Day' => array(
					'visits_module_timeframe_day',
					!empty($context['member']['options']['Breeze_visits_timeframe']) && $context['member']['options']['Breeze_visits_timeframe'] == 'Day' ? 'selected' : ''
				),
				'Week' => array(
					'visits_module_timeframe_week',
					!empty($context['member']['options']['Breeze_visits_timeframe']) && $context['member']['options']['Breeze_visits_timeframe'] == 'Week' ? 'selected' : ''
				),
				'Month' => array(
					'visits_module_timeframe_month',
					!empty($context['member']['options']['Breeze_visits_timeframe']) && $context['member']['options']['Breeze_visits_timeframe'] == 'Month' ? 'selected' : ''
				),
			)
		);

		/* Send the form to the template */
		$context['Breeze']['UserSettings']['Form'] = $form->display();
	}

	public static function notifications()
	{
		global $context, $user_info, $scripturl;

		loadtemplate(Breeze::$name);

		/* Load all we need */
		$query = Breeze::query();
		$text = Breeze::text();
		$data = $query->getSettingsByUser($context['member']['id']);
		$globals = Breeze::sGlobals('request');

		/* Set all the page stuff */
		$context['sub_template'] = 'user_notifications';
		$context['page_title'] = $text->getText('noti_title');
		$context['user']['is_owner'] = $context['member']['id'] == $user_info['id'];
		$context['canonical_url'] = $scripturl . '?action=profile;area=notifications;u=' . $context['member']['id'];
	}

	/* Show the buddy request list */
	public static function buddyRequest()
	{
		global $context, $user_info, $scripturl, $memberContext;

		/* Do a quick check to ensure people aren't getting here illegally! */
		if (!$context['user']['is_owner'])
			fatal_lang_error('no_access', false);

		loadtemplate('BreezeBuddy');

		/* Load all we need */
		$buddies = Breeze::buddies();
		$text = Breeze::text();
		$globals = Breeze::sGlobals('request');
		$query = Breeze::query();

		/* Set all the page stuff */
		$context['sub_template'] = 'Breeze_buddy_list';
		$context['page_title'] = $text->getText('noti_title');
		$context['user']['is_owner'] = $context['member']['id'] == $user_info['id'];
		$context['canonical_url'] = $scripturl . '?action=profile;area=breezebuddies;u=' . $context['member']['id'];

		/* Show a nice message for confirmation */
		if ($globals->validate('inner') == true)
			switch ($globals->getRaw('inner'))
			{
				case 1:
					$context['Breeze']['inner_message'] = $text->getText('buddyrequest_confirmed_inner_message');
					break;
				case 2:
					$context['Breeze']['inner_message'] = $text->getText('buddyrequest_confirmed_inner_message_de');
					break;
				default:
					$context['Breeze']['inner_message'] = '';
					break;
			}

		else
			$context['Breeze']['inner_message'] = '';

		/* Send the buddy request(s) to the template */
		$context['Breeze']['Buddy_Request'] = $buddies->showBuddyRequests($context['member']['id']);

		if ($globals->validate('from') == true && $globals->validate('confirm') == true && $user_info['id'] != $globals->getValue('from'))
		{
			/* Load Subs-Post to use sendpm */
			Breeze::load('Subs-Post');

			/* ...and a new friendship is born, yay! */
			$user_info['buddies'][] = $globals->getValue('from');
			$context['Breeze']['user_info'][$globals->getValue('from')]['buddies'][] = $user_info['id'];

			/* Update both users buddy array. */
			updateMemberData($user_info['id'], array('buddy_list' => implode(',', $user_info['buddies'])));
			updateMemberData($globals->getValue('from'), array('buddy_list' => implode(',', $context['Breeze']['user_info'][$globals->getValue('from')]['buddies'])));

			/* Send a pm to the user */
			$recipients = array(
				'to' => array($globals->getValue('from')),
				'bcc' => array(),
			);

			/* @todo make this a guest account */
			$from = array(
				'id' => $user_info['id'],
				'name' => $user_info['name'],
				'username' => $user_info['username'],
			);

			/* @todo let the user to send a customized message/title */
			$subject = $text->getText('buddyrequest_confirmed_subject');
			$message = sprintf($text->getText('buddyrequest_confirmed_message'), $user_info['link']);

			sendpm($recipients, $subject, $message, false, $from);

			/* Destroy the notification */
			$query->DeleteNotification($globals->getRaw('confirm'));


			/* Redirect back to the profile buddy request page*/
			redirectexit('action=profile;area=breezebuddies;inner=1;u=' . $user_info['id']);
		}

		/* Declined? */
		elseif ($globals->validate('decline') == true)
		{
			/* Delete the notification */
			$query->DeleteNotification($globals->getRaw('decline'));

			/* Redirect back to the profile buddy request page*/
			redirectexit('action=profile;area=breezebuddies;inner=2;u=' . $user_info['id']);
		}
	}

	/* Show a message to let the user know their buddy request must be approved */
	public static function buddyMessageSend()
	{
		global $context, $user_info, $scripturl;

		loadtemplate('BreezeBuddy');

		$text = Breeze::text();

		/* Set all the page stuff */
		$context['sub_template'] = 'Breeze_request_buddy_message_send';
		$context['page_title'] = $text->getText('noti_title');
		$context['canonical_url'] = $scripturl . '?action=breezebuddyrequest';
	}

	/* Show a single status with all it's comments */
	public static function single()
	{
		global $context, $user_info, $scripturl;

		loadtemplate(Breeze::$name);

		/* Load what we need */
		$text = Breeze::text();
		$globals = Breeze::sGlobals('post');
		$settings = Breeze::settings();
		$query = Breeze::query();

		/* We are gonna load the status from the user array so we kinda need both the user ID and a status ID */
		if (!$globals->validate('u') || !$globals->validate('bid'))
			fatal_lang_error('no_access', false);

		/* Load the single status */
		$context['Breeze']['single'] = $query->getStatusByID($globals->getValue('bid'), $globals->getValue('u'));

		/* Set all the page stuff */
		$context['sub_template'] = 'Breeze_show_single_status';
		$context['page_title'] = $text->getText('noti_title');
		$context['canonical_url'] = $scripturl .'?action=profile;area=wallstatus';
	}
}