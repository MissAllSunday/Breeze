<?php

/**
 * Breeze_User
 *
 * The purpose of this file is To show the user wall and provide a settings page
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

	/* A bunch of wrapper functions so static methods can be callable with a string by SMF */
	function Breeze_Wrapper_Wall(){Breeze_User::Wall();}
	function Breeze_Wrapper_Settings(){Breeze_User::Settings();}
	function Breeze_Wrapper_BuddyRequest(){Breeze_User::BuddyRequest();}
	function Breeze_Wrapper_BuddyMessageSend(){Breeze_User::BuddyMessageSend();}
	function Breeze_Wrapper_Notifications(){Breeze_User::Notifications();}

class Breeze_User
{
	public function  __construct(){}

	public static function Wall()
	{
		global $txt, $scripturl, $context, $memberContext, $modSettings,  $user_info;

		loadtemplate('Breeze');
		Breeze::Load(array(
			'Settings',
			'Subs',
			'UserInfo',
			'Modules',
			'Query',
			'Pagination',
			'Globals'
		));

		/* We kinda need all this stuff, dont' ask why, just nod your head... */
		$settings = Breeze_Settings::getInstance();
		$query = Breeze_Query::getInstance();
		$tools = new Breeze_Subs();
		$modules = new Breeze_Modules($context['member']['id']);
		$globals = new Breeze_Globals('get');

		/* Another page already checked the permissions and if the mod is enable, but better be safe... */
		if (!$settings->Enable('admin_settings_enable'))
			redirectexit();

		/* Load this user's settings */
		$user_settings = $query->GetUserSettings($context['member']['id']);

		/* Does the user even enable this? */
		if ($user_settings['enable_wall'] == 0)
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
		if (empty($context['member']['ignore_list']))
			$context['member']['ignore_list'] = $query->GetUserIgnoreList($context['member']['id']);

		/* I'm sorry, you aren't allowed in here, but here's a nice static page :) */
		if (in_array($user_info['id'], $context['member']['ignore_list']) && $user_settings['kick_ignored'] == 1)
			redirectexit('action=profile;area=static;u='.$context['member']['id']);

		/* Display all the JavaScript bits */
		$tools->Headers();

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

		$users_to_load = array();

		/* Load all the status */
		$status = $query->GetStatusByProfile($context['member']['id']);

		/* Collect the IDs to build their profile's lightbox and also load the comments */
		foreach($status as $k => $s)
		{
			$users_to_load[] = $s['poster_id'];

			/* Load the comments for each status */
			$status[$k]['comments'] = $query->GetCommentsByStatus($s['id']);

			/* Get the user id from the comments */
			if ($status[$k]['comments'])
				foreach($status[$k]['comments'] as $c)
					$users_to_load[] = $c['poster_id'];

			else
				$status[$k]['comments'] = array();
		}

		/* Getting the current page. */
		$page = $globals->Validate('page') == true ? $globals->Raw('page') : 1;

		/* Applying pagination. */
		$pagination = new Breeze_Pagination($status, $page, '?action=profile;page=', '', !empty($user_settings['pagination_number']) ? $user_settings['pagination_number'] : 5, 5);
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

		/* We have all the IDs, let's prune the array a little */
		$new_temp_array = array_unique($users_to_load);

		/* Load the data */
		loadMemberData($new_temp_array, false, 'profile');
		foreach($new_temp_array as $u)
		{
			loadMemberContext($u);
			$user = $memberContext[$u];
			$context['Breeze']['user_info'][$user['id']] = Breeze_UserInfo::Profile($user);
		}

		/* We don't need this anymore */
		unset($new_temp_array);
		unset($users_to_load);

		/* Modules */
		$context['Breeze']['Modules'] = $modules->GetAllModules();

		/* The visitor's permissions */
		$context['Breeze']['visitor']['post_status'] = allowedTo('breeze_postStatus');
		$context['Breeze']['visitor']['post_comment'] = allowedTo('breeze_postComments');
		$context['Breeze']['visitor']['delete_status_comments'] = allowedTo('breeze_deleteStatus');

		/* Write to the log */
		$query->WriteProfileVisit($context['member']['id'], $user_info['id']);
	}

	/* Shows a form for users to set up their wall as needed. */
	public static function Settings()
	{
		global $context, $user_info, $txt, $scripturl;

		loadtemplate('Breeze');
		Breeze::Load(array(
			'Form',
			'Globals',
			'Query',
			'Settings'
		));

		/* Is this the right user? */
		if ($context['member']['id'] != $user_info['id'])
			redirectexit('action=profile');

		/* By default we set this to false */
		$already = false;

		/* Load all we need */
		$query = Breeze_Query::getInstance();
		$text = Breeze_Settings::getInstance();
		$data = $query->GetSettingsByUser($context['member']['id']);
		$globals = new Breeze_Globals('request');

		if (!empty($data))
			$already = true;

		/* Set all the page stuff */
		$context['sub_template'] = 'user_settings';
		$context['can_send_pm'] = allowedTo('pm_send');
		$context['page_title'] = $text->GetText('user_settings_name');
		$context['user']['is_owner'] = $context['member']['id'] == $user_info['id'];
		$context['canonical_url'] = $scripturl . '?action=profile;area=breezesettings;u=' . $context['member']['id'];

		$FormData = array(
			'action' => 'profile;area=breezesettings;save;u='.$context['member']['id'],
			'method' => 'post',
			'id_css' => 'user_settings_form',
			'name' => 'user_settings_form',
			'class_css' => 'user_settings_form',
			'onsubmit' => '',
		);

		/* The General settings form */
		$form = new Breeze_Form($FormData);

		$form->AddCheckBox('enable_wall', 1, array(
			'enable_wall',
			'enable_wall_sub'
		), !empty($data['enable_wall']) ? true : false);

		$form->AddCheckBox('kick_ignored', 1, array(
			'kick_ignored',
			'kick_ignored_sub'
		), !empty($data['kick_ignored']) ? true : false);

		$form->AddText('pagination_number', !empty($data['pagination_number']) ? $data['pagination_number'] : '', array(
			'pagination_number',
			'pagination_number_sub'
		), 3, 3);

		$form->AddHr();

		$form->AddCheckBox('enable_visits_module', 1, array(
			'enable_visits_module',
			'enable_visits_module_sub'
		), !empty($data['enable_visits_module']) ? true : false);

		$form->AddSelect('visits_module_timeframe', array(
			'visits_module_timeframe',
			'visits_module_timeframe_sub'
		), $values = array(
				1 => array(
					'time_hour',
					!empty($data['visits_module_timeframe']) ? ($data['visits_module_timeframe'] == 1 ? 'selected' : false) : false
				),
				2 => array(
					'time_day',
					!empty($data['visits_module_timeframe']) ? ($data['visits_module_timeframe'] == 2 ? 'selected' : false) : false
				),
				3 => array(
					'time_week',
					!empty($data['visits_module_timeframe']) ? ($data['visits_module_timeframe'] == 3 ? 'selected' : false) : 'selected'
				),
				4 => array(
					'time_month',
					!empty($data['visits_module_timeframe']) ? ($data['visits_module_timeframe'] == 4 ? 'selected' : false) : false
				),
			)
		);

		$form->AddHr();

		$form->AddSubmitButton('save');

		/* Send the form to the template */
		$context['Breeze']['UserSettings']['Form'] = $form->Display();

		/* Saving? */
		if ($globals->Validate('save') == true)
		{
			/* Kill the Settings cache */
			$query->KillCache('Settings');

			$temp = $form->ReturnElementNames();
			$save_data = array();
			$save_data['user_id'] = $context['member']['id'];

			foreach ($temp as &$type)
				$save_data[$type] = !empty($_POST[$type]) ? (int) $_POST[$type] : 0;

			/* If the data already exist, update... */
			if ($already == true)
				$query->UpdateUserSettings($save_data);

			/* ...if not, insert. */
			else
				$query->InsertUserSettings($save_data);

			redirectexit('action=profile;area=breezesettings;u='.$context['member']['id']);
		}
	}

	public static function Notifications()
	{
		global $context, $user_info, $scripturl;

		loadtemplate('Breeze');
		Breeze::Load(array(
			'Notifications',
			'Globals',
			'Query',
			'Settings'
		));

		/* Load all we need */
		$query = Breeze_Query::getInstance();
		$text = Breeze_Settings::getInstance();
		$data = $query->GetSettingsByUser($context['member']['id']);
		$globals = new Breeze_Globals('request');

		/* Set all the page stuff */
		$context['sub_template'] = 'user_notifications';
		$context['page_title'] = $text->GetText('noti_title');
		$context['user']['is_owner'] = $context['member']['id'] == $user_info['id'];
		$context['canonical_url'] = $scripturl . '?action=profile;area=notifications;u=' . $context['member']['id'];
	}

	/* Show the buddy request list */
	public static function BuddyRequest()
	{
		global $context, $user_info, $scripturl, $memberContext;

		/* Do a quick check to ensure people aren't getting here illegally! */
		if (!$context['user']['is_owner'])
			fatal_lang_error('no_access', false);

		loadtemplate('BreezeBuddy');
		Breeze::Load(array(
			'Buddy',
			'Settings',
			'Globals',
			'Query'
		));

		/* Load all we need */
		$buddies = new Breeze_Buddy();
		$text = Breeze_Settings::getInstance();
		$globals = new Breeze_Globals('request');
		$query = Breeze_Query::getInstance();

		/* Set all the page stuff */
		$context['sub_template'] = 'Breeze_buddy_list';
		$context['page_title'] = $text->GetText('noti_title');
		$context['user']['is_owner'] = $context['member']['id'] == $user_info['id'];
		$context['canonical_url'] = $scripturl . '?action=profile;area=breezebuddies;u=' . $context['member']['id'];

		/* Show a nice message for confirmation */
		if ($globals->Validate('inner') == true)
			switch ($globals->Raw('inner'))
			{
				case 1:
					$context['Breeze']['inner_message'] = $text->GetText('buddyrequest_confirmed_inner_message');
					break;
				case 2:
					$context['Breeze']['inner_message'] = $text->GetText('buddyrequest_confirmed_inner_message_de');
					break;
				default:
					$context['Breeze']['inner_message'] = '';
					break;
			}

		else
			$context['Breeze']['inner_message'] = '';

		/* Send the buddy request(s) to the template */
		$context['Breeze']['Buddy_Request'] = $buddies->ShowBuddyRequests($context['member']['id']);

		if ($globals->Validate('from') == true && $globals->Validate('confirm') == true && $user_info['id'] != $globals->See('from'))
		{
			/* Load Subs-Post to use sendpm */
			Breeze::Load('Subs-Post', true);

			$user_info['buddies'][] = $globals->See('from');
			$context['Breeze']['Buddy_Request'][$globals->See('from')]['content']->from_buddies[] = $user_info['id'];

			/* Update both users buddy array. */
			updateMemberData($user_info['id'], array('buddy_list' => implode(',', $user_info['buddies'])));
			updateMemberData($globals->See('from'), array('buddy_list' => implode(',', $context['Breeze']['Buddy_Request'][$globals->See('from')]['content']->from_buddies)));

			/* Send a pm to the user */
			$recipients = array(
				'to' => array($globals->See('from')),
				'bcc' => array(),
			);
			$from = array(
				'id' => $user_info['id'],
				'name' => $user_info['name'],
				'username' => $user_info['username'],
			);

			/* @todo let the user to send a customized message/title */
			$subject = $text->GetText('buddyrequest_confirmed_subject');
			$message = sprintf($text->GetText('buddyrequest_confirmed_message'), $user_info['name']);

			sendpm($recipients, $subject, $message, false, $from);

			/* Destroy the notification */
			$query->DeleteNotification($globals->Raw('confirm'));


			/* Redirect back to the profile buddy request page*/
			redirectexit('action=profile;area=breezebuddies;inner=1;u=' . $user_info['id']);
		}

		/* Declined? */
		elseif ($globals->Validate('decline') == true)
		{
			/* Delete the notification */
			$query->DeleteNotification($globals->Raw('decline'));

			/* Redirect back to the profile buddy request page*/
			redirectexit('action=profile;area=breezebuddies;inner=2;u=' . $user_info['id']);
		}
	}

	/* Show a message to let the user know their buddy request must be approved */
	public static function BuddyMessageSend()
	{
		global $context, $user_info, $scripturl;

		loadtemplate('BreezeBuddy');
		Breeze::Load(array(
			'Settings'
		));

		$text = Breeze_Settings::getInstance();

		/* Set all the page stuff */
		$context['sub_template'] = 'Breeze_request_buddy_message_send';
		$context['page_title'] = $text->GetText('noti_title');
		$context['canonical_url'] = $scripturl . '?action=breezebuddyrequest';
	}
}