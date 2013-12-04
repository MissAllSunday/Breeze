<?php

/**
 * BreezeUser
 *
 * The purpose of this file is To show the user wall and provide a settings page
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

function breezeWall()
{
	global $txt, $scripturl, $context, $memberContext, $sourcedir;
	global $modSettings,  $user_info, $breezeController, $memID, $settings;

	loadtemplate(Breeze::$name);
	loadtemplate(Breeze::$name .'Functions');

	// Madness, madness I say!
	if (empty($breezeController))
		$breezeController = new BreezeController();

	// We kinda need all this stuff, don't ask why, just nod your head...
	$breezeSettings = $breezeController->get('settings');
	$query = $breezeController->get('query');
	$tools = $breezeController->get('tools');
	$globals = Breeze::sGlobals('get');
	$text = $breezeController->get('text');
	$log = $breezeController->get('log');
	$usersToLoad = array();

	// Is owner?
	$context['member']['is_owner'] = $context['member']['id'] == $user_info['id'];

	// Check if this user allowed to be here
	breezeCheckPermissions();

	// We need to make sure we have all your info...
	if (empty($context['Breeze']['user_info'][$user_info['id']]))
		$tools->loadUserInfo($user_info['id']);

	// Default values
	$status = array();
	$context['Breeze'] = array(
		'views' => false,
		'log' => false,
		'buddiesLog' => false,
		'commingFrom' => 'profile',
		'settings' => array(),
	);

	// Get profile owner settings.
	$context['Breeze']['settings']['owner'] = $query->getUserSettings($context['member']['id']);

	// Does the current user (AKA visitor) is also the owner?
	if ($context['member']['is_owner'])
		$context['Breeze']['settings']['visitor'] = $context['Breeze']['settings']['owner'];

	// Nope? :(
	else
		$context['Breeze']['settings']['visitor'] = $query->getUserSettings($user_info['id']);

	// Load al the JS goodies...
	$tools->profileHeaders($context['Breeze']['settings']['visitor']);

	// Set all the page stuff
	$context['sub_template'] = 'user_wall';
	$context += array(
		'can_send_pm' => allowedTo('pm_send'),
		'can_have_buddy' => allowedTo('profile_identity_own') && !empty($modSettings['enable_buddylist']),
		'can_issue_warning' => in_array('w', $context['admin_features']) && allowedTo('issue_warning') && $modSettings['warning_settings'][0] == 1,
	);
	$context['canonical_url'] = $scripturl . '?action=profile;u=' . $context['member']['id'];
	$context['member']['status'] = array();
	$context['Breeze']['tools'] = $tools;
	$context['can_view_warning'] = in_array('w', $context['admin_features']) && (allowedTo('issue_warning') && !$context['member']['is_owner']) || (!empty($modSettings['warning_show']) && ($modSettings['warning_show'] > 1 || $context['member']['is_owner']));

	// You are allowed here but you still need to obey some permissions.
	foreach (Breeze::$permissions as $p)
		$context['Breeze']['permissions'][$p] = $context['member']['is_owner'] ? true : allowedTo('breeze_'. $p);

	// Set up some vars for pagination
	$maxIndex = !empty($context['Breeze']['settings']['visitor']['pagination_number']) ? $context['Breeze']['settings']['visitor']['pagination_number'] : 5;
	$currentPage = $globals->validate('start') == true ? $globals->getValue('start') : 0;

	// Load all the status
	$data = $query->getStatusByProfile($context['member']['id'], $maxIndex, $currentPage);

	// Load users data
	if (!empty($data['users']))
		$usersToLoad = $usersToLoad + $data['users'];

	// Pass the status info
	if (!empty($data['data']))
		$context['member']['status'] = $data['data'];

	// Applying pagination.
	if (!empty($data['pagination']))
		$context['page_index'] = $data['pagination'];

	// Page name depends on pagination
	$context['page_title'] = sprintf($text->getText('profile_of_username'), $context['member']['name']);

	// Get the profile views
	if (!$user_info['is_guest'] && !empty($context['Breeze']['settings']['owner']['enable_visitors']))
	{
		$context['Breeze']['views'] = breezeTrackViews();

		// Load their data
		if (!empty($context['Breeze']['views']))
			$usersToLoad = array_merge($usersToLoad, array_keys($context['Breeze']['views']));
	}

	// Show buddies only if there is something to show
	if (!empty($context['Breeze']['settings']['owner']['enable_buddies']) && !empty($context['member']['buddies']))
		$usersToLoad = $usersToLoad + $context['member']['buddies'];

	// Show this user recent activity
	if (!empty($context['Breeze']['settings']['owner']['enable_activityLog']))
		$context['Breeze']['log'] = $log->getActivity($context['member']['id']);

	// Need to pass some vars to the browser :(
	$context['html_headers'] .= '
	<script type="text/javascript"><!-- // --><![CDATA[
		window.breeze_profileOwner = '. $context['member']['id'] .';
		window.breeze_commingFrom = ' . JavaScriptEscape($context['Breeze']['commingFrom']) . ';
		window.breeze_maxIndex = ' . $maxIndex . ';
		window.breeze_userID = ' . $user_info['id'] . ';
		window.breeze_totalItems = ' . $data['count'] . ';
		window.breeze_loadMore = ' . (!empty($context['Breeze']['settings']['visitor']['load_more']) ? 'true' : 'false') . ';
	// ]]></script>';

	// Lastly, load all the users data from this bunch of user IDs
	if (!empty($usersToLoad))
		$tools->loadUserInfo(array_unique($usersToLoad));
}

// Shows a form for users to set up their wall as needed.
function breezeSettings()
{
	global $context, $memID, $breezeController, $scripturl, $txt, $user_info;

	loadtemplate(Breeze::$name);
	loadtemplate(Breeze::$name .'Functions');

	$sg = Breeze::sGlobals('get');

	if (empty($breezeController))
		$breezeController = new BreezeController();

	// Set the page title
	$context['page_title'] = $breezeController->get('text')->getText('user_settings_name');
	$context['sub_template'] = 'member_options';
	$context['page_desc'] = $breezeController->get('text')->getText('user_settings_enable_desc');
	$context['Breeze_redirect'] = '';

	$context += array(
		'page_title' => $breezeController->get('text')->getText('user_settings_name'),
		'page_desc' => $breezeController->get('text')->getText('user_settings_enable_desc')
	);

	// Get the user settings.
	$userSettings = $breezeController->get('query')->getUserSettings($context['member']['id']);

	// Create the form.
	$form = $breezeController->get('form');

	// Group all these values into an array.
	$form->setFormName('breezeSettings');

	// Session stuff.
	$form->addHiddenField($context['session_var'], $context['session_id']);

	// Per user master setting.
	$form->addCheckBox(
		'enable_wall',
		!empty($userSettings['enable_wall']) ? true : false
	);

	// Pagination.
	$form->addText(
		'pagination_number',
		!empty($userSettings['pagination_number']) ? $userSettings['pagination_number'] : 0,
		3,3
	);

	// Add the load more button.
	$form->addCheckBox(
		'load_more',
		!empty($userSettings['load_more']) ? true : false
	);

	// How many options to be displayed when mentioning.
	$form->addText(
		'how_many_mentions',
		!empty($userSettings['how_many_mentions']) ? $userSettings['how_many_mentions'] : 0,
		3,3
	);

	// Allow ignored users.
	$form->addCheckBox(
		'kick_ignored',
		!empty($userSettings['kick_ignored']) ? true : false
	);

	// Activity Log.
	$form->addCheckBox(
		'enable_activityLog',
		!empty($userSettings['enable_activityLog']) ? true : false
	);

	// Buddies.
	$form->addCheckBox(
		'enable_buddies',
		!empty($userSettings['enable_buddies']) ? true : false
	);

	// Profile visitors.
	$form->addCheckBox(
		'enable_visitors',
		!empty($userSettings['enable_visitors']) ? true : false
	);

	// Visitors timeframe.
	$form->addSelect(
		'visitors_timeframe',
		array(
			'Hour' => array(
				'visitors_timeframe_hour',
				!empty($userSettings['visitors_timeframe']) && $userSettings['visitors_timeframe'] == 'Hour' ? 'selected' : ''
			),
			'Day' => array(
				'visitors_timeframe_day',
				!empty($userSettings['visitors_timeframe']) && $userSettings['visitors_timeframe'] == 'Day' ? 'selected' : ''
			),
			'Week' => array(
				'visitors_timeframe_week',
				!empty($userSettings['visitors_timeframe']) && $userSettings['visitors_timeframe'] == 'Week' ? 'selected' : ''
			),
			'Month' => array(
				'visitors_timeframe_month',
				!empty($userSettings['visitors_timeframe']) && $userSettings['visitors_timeframe'] == 'Month' ? 'selected' : ''
			),
		)
	);

	// Clean visitors log
	$form->addHTML(
		'clean_visitors',
		'<a href="'. $scripturl .'?action=breezeajax;sa=cleanlog;log=visitors;u='. $context['member']['id'] .'">%s</a>'
	);

	// Send the form to the template
	$context['Breeze']['UserSettings']['Form'] = $form->display();
}

function breezenotisettings()
{
	global $context, $memID, $breezeController, $scripturl, $txt, $user_info;

	loadtemplate(Breeze::$name);
	loadtemplate(Breeze::$name .'Functions');

	$sg = Breeze::sGlobals('get');

	if (empty($breezeController))
		$breezeController = new BreezeController();

	// Set the page title
	$context['page_title'] = $breezeController->get('text')->getText('user_settings_name');
	$context['sub_template'] = 'member_options';
	$context['page_desc'] = $breezeController->get('text')->getText('user_settings_enable_desc');

	$context += array(
		'page_title' => $breezeController->get('text')->getText('user_settings_name'),
		'page_desc' => $breezeController->get('text')->getText('user_settings_enable_desc')
	);

	// Need to tell the form the page it needs to display when redirecting back after saving.
	$context['Breeze_redirect'] = 'breezenotisettings';

	// Get the user settings.
	$userSettings = $breezeController->get('query')->getUserSettings($context['member']['id']);

	// Create the form.
	$form = $breezeController->get('form');

	// Group all these values into an array.
	$form->setFormName('breezeSettings');

	// Notification settings
	$form->addSection('name_settings');

	// How many seconds before closing the notifications?
	$form->addText(
		'clear_noti',
		!empty($userSettings['clear_noti']) ? $userSettings['clear_noti'] : 0,
		3,
		3
	);

	// Noti on comment
	$form->addCheckBox(
		'noti_on_comment',
		!empty($userSettings['noti_on_comment']) ? true : false
	);

	// Noti on mention
	$form->addCheckBox(
		'noti_on_mention',
		!empty($userSettings['noti_on_mention']) ? true : false
	);

	// Send the form to the template
	$context['Breeze']['UserSettings']['Form'] = $form->display();
}

function breezeNotifications()
{
	global $context, $user_info, $scripturl, $options, $breezeController;

	loadtemplate(Breeze::$name);
	loadtemplate(Breeze::$name .'Functions');

	if (empty($breezeController))
		$breezeController = new BreezeController();

	$breezeController->get('tools')->profileHeaders();

	// Globals...
	$globals = Breeze::sGlobals('request');

	// We kinda need all this stuff, don't ask why, just nod your head...
	$query = $breezeController->get('query');
	$text = $breezeController->get('text');
	$notifications = $breezeController->get('notifications');
	$tools = $breezeController->get('tools');
	$tempNoti = $query->getNotificationByReceiver($context['member']['id'], true);

	// Load the users data
	if (!empty($tempNoti['users']))
		$tools->loadUserInfo($tempNoti['users']);

	// Pass the info to the template
	if ($notifications->prepare($context['member']['id'], true))
		$context['Breeze']['noti'] = $notifications->getMessages();

	// Tell everyone where we've been
	$context['Breeze']['commingFrom'] = 'profile';

	// Set all the page stuff
	$context['sub_template'] = 'user_notifications';
	$context['page_title'] = $text->getText('noti_title');
	$context['member']['is_owner'] = $context['member']['id'] == $user_info['id'];
	$context['canonical_url'] = $scripturl . '?action=profile;area=notifications;u=' . $context['member']['id'];

	// Print some jQuery goodies...
	$context['html_headers'] .= '
	<script type="text/javascript"><!-- // --><![CDATA[
		jQuery(document).on(\'change\', \'input[name="check_all"]\',function() {
			jQuery(\'.idNoti\').prop("checked" , this.checked);
		});
	// ]]></script>';
}

// Show the buddy request list
function breezeBuddyRequest()
{
	global $context, $user_info, $scripturl, $memberContext, $breezeController;

	// Do a quick check to ensure people aren't getting here illegally!
	if (!$context['member']['is_owner'])
		fatal_lang_error('no_access', false);

	loadtemplate(Breeze::$name);
	loadtemplate(Breeze::$name .'Functions');

	if (empty($breezeController))
		$breezeController = new BreezeController();

	$breezeController->get('tools')->profileHeaders();

	// Load all we need
	$buddies = $breezeController->get('buddy');
	$text = $breezeController->get('text');
	$globals = Breeze::sGlobals('request');
	$query = $breezeController->get('query');

	// Set all the page stuff
	$context['sub_template'] = 'Breeze_buddy_list';
	$context['page_title'] = $text->getText('noti_title');
	$context['canonical_url'] = $scripturl . '?action=profile;area=breezebuddies;u=' . $context['member']['id'];

	// Show a nice message for confirmation
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

	// Send the buddy request(s) to the template
	$context['Breeze']['Buddy_Request'] = $buddies->showBuddyRequests($context['member']['id']);

	if ($globals->validate('from') == true && $globals->validate('message') == 'confirm' && $user_info['id'] != $globals->getValue('from'))
	{
		// Load Subs-Post to use sendpm
		Breeze::load('Subs-Post');

		// ...and a new friendship is born, yay!
		$user_info['buddies'][] = $globals->getValue('from');
		$context['Breeze']['user_info'][$globals->getValue('from')]['buddies'][] = $user_info['id'];

		// Update both users buddy array.
		updateMemberData($user_info['id'], array('buddy_list' => implode(',', $user_info['buddies'])));
		updateMemberData($globals->getValue('from'), array('buddy_list' => implode(',', $context['Breeze']['user_info'][$globals->getValue('from')]['buddies'])));

		// Send a pm to the user
		$recipients = array(
			'to' => array($globals->getValue('from')),
			'bcc' => array(),
		);

		// @todo make this a guest account
		$from = array(
			'id' => $user_info['id'],
			'name' => $user_info['name'],
			'username' => $user_info['username'],
		);

		// @todo let the user to send a customized message/title
		$subject = $text->getText('buddyrequest_confirmed_subject');
		$message = $text->getText('buddyrequest_confirmed_message');
		$noti = $globals->getValue('noti');

		sendpm($recipients, $subject, $message, false, $from);

		// Destroy the notification
		$query->deleteNoti($noti, $user_info['id']);

		// Redirect back to the profile buddy request page
		redirectexit('action=profile;area=breezebuddies;inner=1;u=' . $user_info['id']);
	}

	// Declined?
	elseif ($globals->validate('message') == 'decline')
	{
		$noti = $globals->getValue('noti');

		// Destroy the notification
		$query->deleteNoti($noti, $user_info['id']);

		// Redirect back to the profile buddy request page
		redirectexit('action=profile;area=breezebuddies;inner=2;u=' . $user_info['id']);
	}
}

/* // Show all possible message regarding the buddy system
function breezeBuddyMessage()
{
	global $context, $scripturl, $breezeController;

	loadtemplate('BreezeBuddy');

	// Get the params
	$globals = Breeze::sGlobals('request');
	$message = $globals->getValue('message')

	// Get on the guest list!
	if (empty($message))
		redirectexit('action=profile');

	// Lets stuff the memory!
	if (empty($breezeController))
		$breezeController = new BreezeController();

	// Load all we need
	$text = $breezeController->get('text');

	// Set all the page stuff
	$context['sub_template'] = 'Breeze_buddy_message';
	$context['page_title'] = $text->getText('noti_title');
	$context['canonical_url'] = $scripturl . '?action=breezebuddyrequest';

	// Linktree here someday!

} */

function breezeTrackViews()
{
	global $user_info, $context, $breezeController;

	$data = array();

	// Don't log guest views
	if ($user_info['is_guest'] == true)
		return false;

	if (empty($breezeController))
		$breezeController = new BreezeController();

	// Do this only if t hasn't been done before
	$views = cache_get_data(Breeze::$name .'-tempViews-'. $context['member']['id'].'-by-'. $user_info['id'], 60);

	if (empty($views))
	{
		// Get the profile views
		$views = $breezeController->get('query')->getViews($context['member']['id']);

		// Don't track own views
		if ($context['member']['id'] == $user_info['id'])
			return !empty($views) ? json_decode($views, true) : false;

		// Don't have any views yet?
		if (empty($views))
		{
			// Build the array
			$views[$user_info['id']] = array(
				'user' => $user_info['id'],
				'last_view' => time(),
				'views' => 1,
			);

			// Insert the data
			updateMemberData($context['member']['id'], array('breeze_profile_views' => json_encode($views)));

			// Set the temp cache
			cache_put_data(Breeze::$name .'-tempViews-'. $context['member']['id'].'-by-'. $user_info['id'], $views, 60);

			// Load the visitors data
			$breezeController->get('tools')->loadUserInfo(array_keys($views));

			// Cut it off
			return $views;
		}

		// Get the data
		$views = json_decode($views, true);

		// Does this member has been here before?
		if (!empty($views[$user_info['id']]))
		{
			// Update the data then
			$views[$user_info['id']]['last_view'] = time();
			$views[$user_info['id']]['views'] = $views[$user_info['id']]['views'] + 1;
		}

		// First time huh?
		else
		{
			// Build the array
			$views[$user_info['id']] = array(
				'user' => $user_info['id'],
				'last_view' => time(),
				'views' => 1,
			);
		}

		// Either way, update the table
		updateMemberData($context['member']['id'], array('breeze_profile_views' => json_encode($views)));

		// ...and set the temp cache
		cache_put_data(Breeze::$name .'-tempViews-'. $context['member']['id'].'-by-'. $user_info['id'], $views, 60);
	}

	// Don't forget to load the visitors data
	$breezeController->get('tools')->loadUserInfo(array_keys($views));

	return $views;
}

function breezeCheckPermissions()
{
	global $context, $memberContext, $user_info, $breezeController;

	if (empty($breezeController))
		$breezeController = new BreezeController();

	$breezeSettings = $breezeController->get('settings');
	$query = $breezeController->get('query');

	// Another page already checked the permissions and if the mod is enable, but better be safe...
	if (!$breezeSettings->enable('admin_settings_enable'))
		redirectexit();

	// If we are forcing the wall, lets check the admin setting first
	if ($breezeSettings->enable('admin_settings_force_enable'))
		if (!isset($context['member']['options']['enable_wall']))
			$context['member']['options']['enable_wall'] = 1;

	// Do the normal check, do note this is not an elseif check, its separate.
	else
		if (empty($context['member']['options']['enable_wall']))
			redirectexit('action=profile;area=static;u='.$context['member']['id']);

	// This user cannot see his/her own profile and cannot see any profile either
	if (!allowedTo('profile_view_own') && !allowedTo('profile_view_any'))
		redirectexit('action=profile;area=static;u='. $context['member']['id']);

	// This user cannot see his/her own profile and it's viewing his/her own profile
	if (!allowedTo('profile_view_own') && $user_info['id'] == $context['member']['id'])
		redirectexit('action=profile;area=static;u='. $context['member']['id']);

	// This user cannot see any profile and it's  viewing someone else's wall
	if (!allowedTo('profile_view_any') && $user_info['id'] != $context['member']['id'])
		redirectexit('action=profile;area=static;u='. $context['member']['id']);

	// Get the ignored list. @todo turn this into a function or something.

	// I'm sorry, you aren't allowed in here, but here's a nice static page :)
	if (!empty($context['member']['ignore_list']) && is_array($context['member']['ignore_list']) && in_array($user_info['id'], $context['member']['ignore_list']) && !empty($context['member']['options']['kick_ignored']))
		redirectexit('action=profile;area=static;u='.$context['member']['id']);
}
