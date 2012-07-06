<?php

/**
 * BreezeUser
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
	function Breeze_Wrapper_Wall(){BreezeUser::Wall();}
	function Breeze_Wrapper_Settings(){BreezeUser::Settings();}
	function Breeze_Wrapper_BuddyRequest(){BreezeUser::BuddyRequest();}
	function Breeze_Wrapper_BuddyMessageSend(){BreezeUser::BuddyMessageSend();}
	function Breeze_Wrapper_Notifications(){BreezeUser::Notifications();}
	function Breeze_Wrapper_Single(){BreezeUser::Single();}

class BreezeUser
{
	public function  __construct(){}

	public static function Wall()
	{
		global $txt, $scripturl, $context, $memberContext, $modSettings,  $user_info;

		loadtemplate('Breeze');

		/* We kinda need all this stuff, dont' ask why, just nod your head... */
		$settings = Breeze::settings();
		$query = Breeze::query();
		$tools = Breeze::tools();
		$globals = Breeze::sGlobals('get');

		/* Another page already checked the permissions and if the mod is enable, but better be safe... */
		if (!$settings->enable('admin_settings_enable'))
			redirectexit();

		/* Load this user's settings */
		$user_settings = Breeze::userSettings($context['member']['id']);

		/* Does the user even enable this? */
		if (!$user_settings->enable('enable_wall'))
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
			$context['member']['ignore_list'] = $user_settings->getUserIgnoreList();

		/* I'm sorry, you aren't allowed in here, but here's a nice static page :) */
		if (in_array($user_info['id'], $context['member']['ignore_list']) && $user_settings->enable('kick_ignored'))
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

		$users_to_load = array();

		/* Load all the status */
		$status = $query->getStatusByProfile($context['member']['id']);


		/* Collect the IDs to build their profile's lightbox and also load the comments */
		foreach($status as $k => $s)
		{
			$users_to_load[] = $s['poster_id'];

			/* Load the comments for each status */
			$status[$k]['comments'] = $query->getCommentsByStatus($s['id']);

			/* Get the user id from the comments */
			if ($status[$k]['comments'])
				foreach($status[$k]['comments'] as $c)
					$users_to_load[] = $c['poster_id'];

			else
				$status[$k]['comments'] = array();
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

		/* We have all the IDs, let's prune the array a little */
		$new_temp_array = array_unique($users_to_load);

		/* Load the data */
		loadMemberData($new_temp_array, false, 'profile');
		foreach($new_temp_array as $u)
		{
			loadMemberContext($u);
			$user = $memberContext[$u];
			$context['Breeze']['user_info'][$user['id']] = BreezeUserInfo::Profile($user);
		}

		/* We don't need this anymore */
		unset($new_temp_array);
		unset($users_to_load);

		/* The visitor's permissions */
		$context['Breeze']['visitor']['post_status'] = allowedTo('breeze_postStatus') || $context['user']['is_owner'];
		$context['Breeze']['visitor']['post_comment'] = allowedTo('breeze_postComments') || $context['user']['is_owner'];
		$context['Breeze']['visitor']['delete_status_comments'] = allowedTo('breeze_deleteStatus') || $context['user']['is_owner'];
	}

	/* Shows a form for users to set up their wall as needed. */
	public static function Settings()
	{
		global $context, $user_info, $scripturl;

		loadtemplate('Breeze');

		/* Is this the right user? */
		if ($context['member']['id'] != $user_info['id'])
			redirectexit('action=profile');

		/* By default we set this to false */
		$already = false;

		/* Load all we need */
		$query = Breeze::query();
		$text = Breeze::text();
		$data = Breeze::userSettings($context['member']['id']);
		$userSettings = $data->getUserSettings();
		$globals = Breeze::sGlobals('request');

		if (!empty($userSettings))
			$already = true;

		/* Set all the page stuff */
		$context['sub_template'] = 'user_settings';
		$context['can_send_pm'] = allowedTo('pm_send');
		$context['page_title'] = $text->getText('user_settings_name');
		$context['user']['is_owner'] = $context['member']['id'] == $user_info['id'];
		$context['canonical_url'] = $scripturl . '?action=profile;area=breezesettings;u=' . $context['member']['id'];

		$formData = array(
			'action' => 'profile;area=breezesettings;save;u='. $context['member']['id'],
			'method' => 'post',
			'id_css' => 'user_settings_form',
			'name' => 'user_settings_form',
			'class_css' => 'user_settings_form',
			'onsubmit' => '',
		);

		/* The General settings form */
		$form = new BreezeForm($formData);

		$form->addCheckBox('enable_wall', 1, array(
			'enable_wall',
			'enable_wall_sub'
		), !empty($userSettings['enable_wall']) ? true : false);

		$form->addCheckBox('kick_ignored', 1, array(
			'kick_ignored',
			'kick_ignored_sub'
		), !empty($userSettings['kick_ignored']) ? true : false);

		$form->addHr();

		$form->addSubmitButton('save');

		/* Send the form to the template */
		$context['Breeze']['UserSettings']['Form'] = $form->display();

		/* Saving? */
		if ($globals->validate('save') == true)
		{
			/* Kill the  cache */
			$query->killCache('members');

			$temp = $form->returnElementNames();
			$save_data = array();
			$save_data['id_member'] = $context['member']['id'];

			foreach ($temp as &$type)
				$save_data[$type] = !empty($_POST[$type]) ? (int) $_POST[$type] : 0;

			/* If the data already exist, update... */
			if ($already == true)
				$data->updateUserSettings($save_data);

			/* ...if not, insert. */
			else
				$data->insertUserSettings($save_data);

			redirectexit('action=profile;area=breezesettings;u='.$context['member']['id']);
		}
	}

	public static function Notifications()
	{
		global $context, $user_info, $scripturl;

		loadtemplate('Breeze');

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
	public static function BuddyRequest()
	{
		global $context, $user_info, $scripturl, $memberContext;

		/* Do a quick check to ensure people aren't getting here illegally! */
		if (!$context['user']['is_owner'])
			fatal_lang_error('no_access', false);

		loadtemplate('BreezeBuddy');

		/* Load all we need */
		$buddies = new BreezeBuddy();
		$text = Breeze::text();
		$globals = new BreezeGlobals('request');
		$query = BreezeQuery::getInstance();

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
	public static function BuddyMessageSend()
	{
		global $context, $user_info, $scripturl;

		loadtemplate('BreezeBuddy');

		$text = Breeze::text();

		/* Set all the page stuff */
		$context['sub_template'] = 'Breeze_request_buddy_message_send';
		$context['page_title'] = $text->getText('noti_title');
		$context['canonical_url'] = $scripturl . '?action=breezebuddyrequest';
	}
}