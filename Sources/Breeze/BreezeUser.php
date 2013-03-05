<?php

/**
 * BreezeUser
 *
 * The purpose of this file is To show the user wall and provide a settings page
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <missallsunday@simplemachines.org>
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

	/* A bunch of ugly, very ugly wrapper functions because static methods cannot be callable with a string by SMF meh... */
	function Breeze_Wrapper_Wall(){BreezeUser::wall();}
	function Breeze_Wrapper_Settings(){BreezeUser::settings();}
	function Breeze_Wrapper_BuddyRequest(){BreezeUser::buddyRequest();}
	function Breeze_Wrapper_BuddyMessageSend(){BreezeUser::buddyMessageSend();}
	function Breeze_Wrapper_Notifications(){BreezeUser::notifications();}
	function Breeze_Wrapper_Single(){BreezeUser::single();}

	function wall()
	{
		global $txt, $scripturl, $context, $memberContext, $modSettings,  $user_info, $breezeController;

		loadtemplate(Breeze::$name);

		/* Check if this user is welcomed here */
		self::checkPermissions();

		if (empty($breezeController))
			$breezeController = new BreezeController();

		/* We kinda need all this stuff, dont' ask why, just nod your head... */
		$settings = $breezeController->get('settings');
		$query = $breezeController->get('query');
		$tools = $breezeController->get('tools');
		$globals = Breeze::sGlobals('get');
		$text = $breezeController->get('text');

		/* Display all the JavaScript bits */
		Breeze::headersHook('profile');

		/* Default values */
		$status = array();

		/* Set all the page stuff */
		$context['sub_template'] = 'user_wall';
		$context += array(
			'can_send_pm' => allowedTo('pm_send'),
			'can_have_buddy' => allowedTo('profile_identity_own') && !empty($modSettings['enable_buddylist']),
			'can_issue_warning' => in_array('w', $context['admin_features']) && allowedTo('issue_warning') && $modSettings['warning_settings'][0] == 1,
		);
		$context['user']['is_owner'] = $context['member']['id'] == $user_info['id'];
		$context['canonical_url'] = $scripturl . '?action=profile;u=' . $context['member']['id'];
		$context['member']['status'] = array();

		/* Load all the status */
		$status = $query->getStatusByProfile($context['member']['id']);

		/* Getting the current page. */
		$page = $globals->validate('page') == true ? $globals->getValue('page') : 1;

		/* Applying pagination. */
		$pagination = new BreezePagination($status, $page, '?action=profile;u='. $context['member']['id'] .';page=', '', !empty($context['member']['options']['Breeze_pagination_number']) ? $context['member']['options']['Breeze_pagination_number'] : 5, 5);
		$pagination->PaginationArray();
		$pagtrue = $pagination->PagTrue();
		$currentPage = $page > 1 ? $txt['Breeze_pag_page'] . $page : '';

		/* Send the array to the template if there is pagination */
		$context['member']['status'] = !empty($pagtrue) ? $pagination->OutputArray() : $status;
		$context['Breeze']['pagination']['panel'] = !empty($pagtrue) ? $pagination->OutputPanel() . $pagination->OutputNext() : '';

		/* Page name depends on pagination */
		$context['page_title'] = sprintf($text->getText('profile_of_username'), $context['member']['name'], $currentPage);
	}

	/* Shows a form for users to set up their wall as needed. */
	function settings()
	{
		global $context, $memID, $breezeController;

		Breeze::load('Profile-Modify');
		loadtemplate(Breeze::$name);

		loadThemeOptions($memID);
		if (allowedTo(array('profile_extra_own')))
			loadCustomFields($memID, 'theme');

		if (empty($breezeController))
			$breezeController = new BreezeController();

		$context['Breeze']['text'] = $breezeController->get('text');
		$context['sub_template'] = 'member_options';
		$context['page_desc'] = $context['Breeze']['text']->getText('user_settings_enable_desc');

		$context += array(
			'page_title' => $context['Breeze']['text']->getText('user_settings_name'),
			'page_desc' => $context['Breeze']['text']->getText('user_settings_enable_desc')
		);

		/* Create the form */
		$form = $breezeController->get('form');

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

	function notifications()
	{
		global $context, $user_info, $scripturl, $options, $breezeController;

		loadtemplate(Breeze::$name);

		/* Display all the JavaScript bits */
		Breeze::headersHook('profile');

		if (empty($breezeController))
			$breezeController = new BreezeController();

		/* We kinda need all this stuff, dont' ask why, just nod your head... */
		$query = $breezeController->get('query');
		$text = $breezeController->get('text');
		$globals = Breeze::sGlobals('request');
		$notifications = $breezeController->get('notifications');
		$context['Breeze']['noti'] = $notifications->getToUser($context['member']['id'], true);

		/* Set all the page stuff */
		$context['sub_template'] = 'user_notifications';
		$context['page_title'] = $text->getText('noti_title');
		$context['user']['is_owner'] = $context['member']['id'] == $user_info['id'];
		$context['canonical_url'] = $scripturl . '?action=profile;area=notifications;u=' . $context['member']['id'];
	}

	/* Show the buddy request list */
	function buddyRequest()
	{
		global $context, $user_info, $scripturl, $memberContext, $breezeController;

		/* Do a quick check to ensure people aren't getting here illegally! */
		if (!$context['user']['is_owner'])
			fatal_lang_error('no_access', false);

		loadtemplate('BreezeBuddy');

		if (empty($breezeController))
			$breezeController = new BreezeController();

		/* Load all we need */
		$buddies = $breezeController->get('buddy');
		$text = $breezeController->get('text');
		$globals = Breeze::sGlobals('request');
		$query = $breezeController->get('query');

		/* Set all the page stuff */
		$context['sub_template'] = 'Breeze_buddy_list';
		$context['page_title'] = $text->getText('noti_title');
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
	function buddyMessageSend()
	{
		global $context, $scripturl, $breezeController;

		loadtemplate('BreezeBuddy');

		if (empty($breezeController))
			$breezeController = new BreezeController();

		/* Load all we need */
		$text = $breezeController->get('text');

		/* Set all the page stuff */
		$context['sub_template'] = 'Breeze_request_buddy_message_send';
		$context['page_title'] = $text->getText('noti_title');
		$context['canonical_url'] = $scripturl . '?action=breezebuddyrequest';
	}

	/* Show a single status with all it's comments */
	function single()
	{
		global $txt, $scripturl, $context, $memberContext, $modSettings,  $user_info, $breezeController;

		loadtemplate(Breeze::$name);

		/* Check if this user is welcomed here */
		self::checkPermissions();

		/* Load what we need */
		$text = $breezeController->get('text');
		$globals = Breeze::sGlobals('get');
		$settings = $breezeController->get('settings');
		$query = $breezeController->get('query');
		$tools = $breezeController->get('tools');

		/* Display all the JavaScript bits */
		Breeze::headersHook('profile');

		/* We are gonna load the status from the user array so we kinda need both the user ID and a status ID */
		if (!$globals->validate('u') || !$globals->validate('bid'))
			fatal_lang_error('no_access', false);

		/* Load the single status */
		$context['Breeze']['single'] = $query->getStatusByID($globals->getValue('bid'), $globals->getValue('u'));

		/* Set all the page stuff */
		$context['sub_template'] = 'singleStatus';
		$context['page_title'] = $text->getText('singleStatus_pageTitle');
		$context['canonical_url'] = $scripturl .'?action=profile;area=wallstatus';
	}

	function checkPermissions()
	{
		global $context, $memberContext, $user_info, $breezeController;

		loadtemplate(Breeze::$name);

		$settings = $breezeController->get('settings');
		$query = $breezeController->get('query');

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

		/* You are allowed here but you still need to obey some permissions */
		$context['Breeze']['visitor']['post_status'] = allowedTo('breeze_postStatus') || $context['user']['is_owner'];
		$context['Breeze']['visitor']['post_comment'] = allowedTo('breeze_postComments') || $context['user']['is_owner'];
		$context['Breeze']['visitor']['delete_status'] = allowedTo('breeze_deleteStatus') || $context['user']['is_owner'];
		$context['Breeze']['visitor']['delete_comments'] = allowedTo('breeze_deleteComments') || $context['user']['is_owner'];
	}