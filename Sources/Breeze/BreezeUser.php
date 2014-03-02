<?php

/**
 * BreezeUser
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica Gonzalez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica Gonzalez
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * breezeWall()
 *
 * Main function, shows the wall, activity, buddies, visitors and any other possible info.
 * @return
 */
function breezeWall()
{
	global $txt, $scripturl, $context, $memberContext, $sourcedir;
	global $modSettings,  $user_info, $breezeController, $memID, $settings;

	loadtemplate(Breeze::$name);
	loadtemplate(Breeze::$name .'Functions');
	loadLanguage(Breeze::$name);

	// Madness, madness I say!
	if (empty($breezeController))
		$breezeController = new BreezeController();

	// We kinda need all this stuff, don't ask why, just nod your head...
	$query = $breezeController->get('query');
	$tools = $breezeController->get('tools');
	$data = Breeze::data('get');
	$log = $breezeController->get('log');
	$usersToLoad = array();

	// Default values.
	$status = array();
	$context['Breeze'] = array(
		'views' => false,
		'log' => false,
		'buddiesLog' => false,
		'comingFrom' => 'profile',
		'settings' => array(
			'owner' => array(),
			'visitor' => array(),
		),
		'compact' => array(
			'visitors' => false,
			'buddy' => false,
		),
	);

	// Does the admin has set a max limit?
	if ($tools->setting('allowed_max_num_users'))
		$context['Breeze']['max_users'] = (int) $tools->setting('allowed_max_num_users');

	// Is owner?
	$context['member']['is_owner'] = $context['member']['id'] == $user_info['id'];

	// Get profile owner settings.
	$context['Breeze']['settings']['owner'] = $query->getUserSettings($context['member']['id']);

	// Check if this user allowed to be here
	breezeCheckPermissions();

	// We need to make sure we have all your info...
	if (empty($context['Breeze']['user_info'][$user_info['id']]))
		$tools->loadUserInfo($user_info['id']);

	// Does the current user (AKA visitor) is also the owner?
	if ($context['member']['is_owner'])
		$context['Breeze']['settings']['visitor'] = $context['Breeze']['settings']['owner'];

	// Nope? :(
	else
		$context['Breeze']['settings']['visitor'] = $query->getUserSettings($user_info['id']);

	// Set all the page stuff
	$context['sub_template'] = 'user_wall';
	$context += array(
		'can_send_pm' => allowedTo('pm_send'),
		'can_have_buddy' => allowedTo('profile_identity_own') && !empty($modSettings['buddylist']),
		'can_issue_warning' => in_array('w', $context['admin_features']) && allowedTo('issue_warning') && $modSettings['warning_settings'][0] == 1,
	);
	$context['canonical_url'] = $scripturl . '?action=profile;u=' . $context['member']['id'];
	$context['member']['status'] = array();
	$context['Breeze']['tools'] = $tools;
	$context['can_view_warning'] = in_array('w', $context['admin_features']) && (allowedTo('issue_warning') && !$context['member']['is_owner']) || (!empty($modSettings['warning_show']) && ($modSettings['warning_show'] > 1 || $context['member']['is_owner']));

	// Set up some vars for pagination
	$maxIndex = !empty($context['Breeze']['settings']['visitor']['pagination_number']) ? $context['Breeze']['settings']['visitor']['pagination_number'] : 5;
	$currentPage = $data->validate('start') == true ? $globals->get('start') : 0;

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
	$context['page_title'] = sprintf($tools->text('profile_of_username'), $context['member']['name']);

	// Get the profile views
	if (!$user_info['is_guest'] && !empty($context['Breeze']['settings']['owner']['visitors']))
	{
		$context['Breeze']['views'] = breezeTrackViews();

		// If there is a limit then lets count the total so we can know if we are gonna use the compact style.
		if (!empty($context['Breeze']['max_users']) && count($context['Breeze']['views']) >= $context['Breeze']['max_users'])
			$context['Breeze']['compact']['visitors'] = true;

		// Nope? then use the user defined value
		else
		{
			// How many visitors are we gonna show?
			if (!empty($context['Breeze']['settings']['owner']['how_many_visitors']) && is_array($context['Breeze']['views']) && count($context['Breeze']['views']) >= $context['Breeze']['settings']['owner']['how_many_visitors'])
				$context['Breeze']['views'] = array_slice($context['Breeze']['views'], 0, $context['Breeze']['settings']['owner']['how_many_visitors']);
		}

		// Load their data
		if (!empty($context['Breeze']['views']))
			$usersToLoad = array_merge($usersToLoad, array_keys($context['Breeze']['views']));
	}

	// Show buddies only if there is something to show
	if (!empty($context['Breeze']['settings']['owner']['buddies']) && !empty($context['member']['buddies']))
	{
		// Hold your horses!
		if (!empty($context['Breeze']['max_users']) && count($context['member']['buddies']) >= $context['Breeze']['max_users'])
			$context['Breeze']['compact']['buddy'] = true;

		$usersToLoad = array_merge($usersToLoad, $context['member']['buddies']);
	}

	// Show this user recent activity
	if (!empty($context['Breeze']['settings']['owner']['activityLog']))
		$context['Breeze']['log'] = $log->getActivity($context['member']['id']);

	// These file are only used here and on the general wall thats why I'm stuffing them here rather than in Breeze::notiHeaders()
	$context['insert_after_template'] .= '
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.caret.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.atwho.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breezeTabs.js"></script>';

	// Are mentions enabled?
	if ($tools->enable('mention'))
		$context['insert_after_template'] .= '
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breezeMention.js"></script>';

	// Does the user wants to use the load more button?
	if (!empty($context['Breeze']['settings']['visitor']['load_more']))
		$context['insert_after_template'] .= '
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breezeLoadMore.js"></script>';

	// Need to pass some vars to the browser :(
	$context['insert_after_template'] .= '
	<script type="text/javascript"><!-- // --><![CDATA[
		breeze.pagination = {
			maxIndex : '. $maxIndex .',
			totalItems : ' . $data['count'] . ',
			userID : '. $context['member']['id'] .'
		};

		breeze.tools.comingFrom = ' . JavaScriptEscape($context['Breeze']['comingFrom']) . ';';

		// Pass the profile owner settings to the client all minus the about me stuff.
		$toClient = $context['Breeze']['settings']['owner'];
		unset($toClient['aboutMe']);
		foreach (Breeze::$allSettings as $k)
			$context['insert_after_template'] .= '
		breeze.ownerSettings.'. $k .' = '. (isset($toClient[$k]) ? (is_array($toClient[$k]) ? json_encode($toClient[$k]) : JavaScriptEscape($toClient[$k])) : 'false') .';';

		unset($toClient);

	// End the js tag
	$context['insert_after_template'] .= '
	// ]]></script>';

	// Lastly, load all the users data from this bunch of user IDs
	if (!empty($usersToLoad))
		$tools->loadUserInfo(array_unique($usersToLoad));
}

/**
 * breezeSettings()
 *
 * Creates a form for each user to configure their wall settings.
 * @return
 */
function breezeSettings()
{
	global $context, $breezeController, $scripturl, $txt, $modSettings;
	global $user_info;

	loadtemplate(Breeze::$name);
	loadtemplate(Breeze::$name .'Functions');

	$sg = Breeze::data('get');

	if (empty($breezeController))
		$breezeController = new BreezeController();

	$tools = $breezeController->get('tools');

	// Is there an admin limit?
	$maxUsers = $tools->setting('allowed_max_num_users') ? $tools->setting('allowed_max_num_users') : 0;

	// Set the page title
	$context['page_title'] = $tools->text('user_settings_name');
	$context['sub_template'] = 'member_options';
	$context['page_desc'] = $tools->text('user_settings_name_desc');
	$context['Breeze_redirect'] = '';

	// Get the user settings.
	$userSettings = $breezeController->get('query')->getUserSettings($context['member']['id']);

	// Create the form.
	$form = $breezeController->get('form');

	// Group all these values into an array. Makes it easier to save the changes.
	$form->setFormName('breezeSettings');

	// Session stuff.
	$form->addHiddenField($context['session_var'], $context['session_id']);

	// Per user master setting.
	$form->addCheckBox(
		'wall',
		!empty($userSettings['wall']) ? true : false
	);

	// General wall setting.
	$form->addCheckBox(
		'general_wall',
		!empty($userSettings['general_wall']) ? true : false
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

	// Activity Log.
	$form->addCheckBox(
		'activityLog',
		!empty($userSettings['activityLog']) ? true : false
	);

	// Only show this is the admin has enable the buddy feature.
	if (!empty($modSettings['enable_buddylist']))
	{
		// Allow ignored users.
		$form->addCheckBox(
			'kick_ignored',
			!empty($userSettings['kick_ignored']) ? true : false
		);

		// Buddies block.
		$form->addCheckBox(
			'buddies',
			!empty($userSettings['buddies']) ? true : false
		);

		// How many buddies are we gonna show?
		$form->addText(
			'how_many_buddies',
			!empty($userSettings['how_many_buddies']) ? ($maxUsers && $userSettings['how_many_buddies'] >= $maxUsers ? $maxUsers : $userSettings['how_many_buddies']) : 0,
			3,3
		);
	}

	// Profile visitors.
	$form->addCheckBox(
		'visitors',
		!empty($userSettings['visitors']) ? true : false
	);

	// How many visitors are we gonna show?
	$form->addText(
		'how_many_visitors',
		!empty($userSettings['how_many_visitors']) ? ($maxUsers && $userSettings['how_many_visitors'] >= $maxUsers ? $maxUsers : $userSettings['how_many_visitors']) : 0,
		3,3
	);

	// Clean visitors log
	$form->addHTML(
		'clean_visitors',
		'<a href="'. $scripturl .'?action=breezeajax;sa=cleanlog;log=visitors;u='. $context['member']['id'] .';rf=profile" class="clean_log">%s</a>'
	);

	// About me textarea.
	$form->addTextArea(
		'aboutMe',
		!empty($userSettings['aboutMe']) ? $userSettings['aboutMe'] : '',
		array('rows' => 10, 'cols' => 50, 'maxLength' => $tools->setting('allowed_maxlength_aboutMe') ? $tools->setting('allowed_maxlength_aboutMe') : 1024)
	);

	// Send the form to the template
	$context['Breeze']['UserSettings']['Form'] = $form->display();
}

/**
 * breezeNotiSettings()
 *
 * Creates notification related settings
 * @return
 */
function breezenotisettings()
{
	global $context, $memID, $breezeController, $scripturl, $txt, $user_info;

	loadtemplate(Breeze::$name);
	loadtemplate(Breeze::$name .'Functions');

	$sg = Breeze::data('get');

	if (empty($breezeController))
		$breezeController = new BreezeController();

	// Set the page title
	$context['page_title'] = $breezeController->get('tools')->text('user_settings_name_settings');
	$context['sub_template'] = 'member_options';
	$context['page_desc'] = $breezeController->get('tools')->text('user_settings_name_settings_desc');

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

	// Noti on status
	$form->addCheckBox(
		'noti_on_status',
		!empty($userSettings['noti_on_status']) ? true : false
	);

	// Noti on comment
	$form->addCheckBox(
		'noti_on_comment',
		!empty($userSettings['noti_on_comment']) ? true : false
	);

	// Noti on comment for profile owner
	$form->addCheckBox(
		'noti_on_comment_owner',
		!empty($userSettings['noti_on_comment_owner']) ? true : false
	);

	// Noti on mention
	$form->addCheckBox(
		'noti_on_mention',
		!empty($userSettings['noti_on_mention']) ? true : false
	);

	// Send the form to the template
	$context['Breeze']['UserSettings']['Form'] = $form->display();
}

/**
 * breezeNotifications()
 *
 * Shows all notifications, both read and unread, options for read, unread and delete.
 * @return
 */
function breezeNotifications()
{
	global $context, $user_info, $scripturl, $options, $breezeController;

	loadtemplate(Breeze::$name);
	loadtemplate(Breeze::$name .'Functions');

	if (empty($breezeController))
		$breezeController = new BreezeController();

	$context['Breeze']['settings']['owner'] = $breezeController->get('query')->getUserSettings($context['member']['id']);

	// Globals...
	$data = Breeze::data('request');

	// We kinda need all this stuff, don't ask why, just nod your head...
	$query = $breezeController->get('query');
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
	$context['Breeze']['comingFrom'] = 'profile';

	// Set all the page stuff
	$context['sub_template'] = 'user_notifications';
	$context['page_title'] = $tools->text('noti_title');
	$context['member']['is_owner'] = $context['member']['id'] == $user_info['id'];
	$context['canonical_url'] = $scripturl . '?action=profile;area=notifications;u=' . $context['member']['id'];
	$context['Breeze']['is_log'] = false;

	// Print some jQuery goodies...
	$context['insert_after_template'] .= '
	<script type="text/javascript"><!-- // --><![CDATA[
		jQuery(document).on(\'change\', \'input[name="check_all"]\',function() {
			jQuery(\'.idNoti\').prop("checked" , this.checked);
		});
	// ]]></script>';
}

/**
 * breezeNotiLogs()
 *
 * Shows all logs, option to mass delete them.
 * @return
 */
function breezeNotiLogs()
{
	global $context, $user_info, $scripturl, $options, $breezeController;

	loadtemplate(Breeze::$name);
	loadtemplate(Breeze::$name .'Functions');

	if (empty($breezeController))
		$breezeController = new BreezeController();

	$context['Breeze']['settings']['owner'] = $breezeController->get('query')->getUserSettings($context['member']['id']);

	// Globals...
	$data = Breeze::data('request');

	// We kinda need all this stuff, don't ask why, just nod your head...
	$logs = $breezeController->get('log')->getActivity($context['member']['id']);
	$tools = $breezeController->get('tools');

	// Pass the info to the template
	$context['Breeze']['noti'] = $logs;

	// Tell everyone where we've been
	$context['Breeze']['comingFrom'] = 'profile';

	// And tell this is a log page...
	$context['Breeze']['is_log'] = true;

	// Set all the page stuff
	$context['sub_template'] = 'user_notifications';
	$context['page_title'] = $tools->text('user_notilogs_name');
	$context['member']['is_owner'] = $context['member']['id'] == $user_info['id'];
	$context['canonical_url'] = $scripturl . '?action=profile;area=breezelogs;u=' . $context['member']['id'];

	// Print some jQuery goodies...
	$context['insert_after_template'] .= '
	<script type="text/javascript"><!-- // --><![CDATA[
		jQuery(document).on(\'change\', \'input[name="check_all"]\',function() {
			jQuery(\'.idNoti\').prop("checked" , this.checked);
		});
	// ]]></script>';
}

/**
 * breezeBuddyRequest()
 *
 * Replaces the standard buddy action in SMF.
 * @return
 */
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

	$context['Breeze']['settings']['owner'] = $query->getUserSettings($context['member']['id']);

	// Load all we need
	$buddies = $breezeController->get('buddy');
	$data = Breeze::data('request');
	$query = $breezeController->get('query');

	// Set all the page stuff
	$context['sub_template'] = 'Breeze_buddy_list';
	$context['page_title'] = $tools->text('noti_title');
	$context['canonical_url'] = $scripturl . '?action=profile;area=breezebuddies;u=' . $context['member']['id'];

	// Show a nice message for confirmation
	if ($data->validate('inner') == true)
		switch ($globals->getRaw('inner'))
		{
			case 1:
				$context['Breeze']['inner_message'] = $tools->text('buddyrequest_confirmed_inner_message');
				break;
			case 2:
				$context['Breeze']['inner_message'] = $tools->text('buddyrequest_confirmed_inner_message_de');
				break;
			default:
				$context['Breeze']['inner_message'] = '';
				break;
		}

	else
		$context['Breeze']['inner_message'] = '';

	// Send the buddy request(s) to the template
	$context['Breeze']['Buddy_Request'] = $buddies->showBuddyRequests($context['member']['id']);

	if ($data->validate('from') == true && $data->validate('message') == 'confirm' && $user_info['id'] != $globals->get('from'))
	{
		// Load Subs-Post to use sendpm
		Breeze::load('Subs-Post');

		// ...and a new friendship is born, yay!
		$user_info['buddies'][] = $globals->get('from');
		$context['Breeze']['user_info'][$globals->get('from')]['buddies'][] = $user_info['id'];

		// Update both users buddy array.
		updateMemberData($user_info['id'], array('buddy_list' => implode(',', $user_info['buddies'])));
		updateMemberData($globals->get('from'), array('buddy_list' => implode(',', $context['Breeze']['user_info'][$globals->get('from')]['buddies'])));

		// Send a pm to the user
		$recipients = array(
			'to' => array($globals->get('from')),
			'bcc' => array(),
		);

		// @todo make this a guest account
		$from = array(
			'id' => $user_info['id'],
			'name' => $user_info['name'],
			'username' => $user_info['username'],
		);

		// @todo let the user to send a customized message/title
		$subject = $tools->text('buddyrequest_confirmed_subject');
		$message = $tools->text('buddyrequest_confirmed_message');
		$noti = $globals->get('noti');

		sendpm($recipients, $subject, $message, false, $from);

		// Destroy the notification
		$query->deleteNoti($noti, $user_info['id']);

		// Redirect back to the profile buddy request page
		redirectexit('action=profile;area=breezebuddies;inner=1;u=' . $user_info['id']);
	}

	// Declined?
	elseif ($data->validate('message') == 'decline')
	{
		$noti = $globals->get('noti');

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
	$data = Breeze::data('request');
	$message = $globals->get('message')

	// Get on the guest list!
	if (empty($message))
		redirectexit('action=profile');

	// Lets stuff the memory!
	if (empty($breezeController))
		$breezeController = new BreezeController();

	// Set all the page stuff
	$context['sub_template'] = 'Breeze_buddy_message';
	$context['page_title'] = $tools->text('noti_title');
	$context['canonical_url'] = $scripturl . '?action=breezebuddyrequest';

	// Linktree here someday!

} */

/**
 * breezeTrackViews()
 *
 * Handles profile views, create or update views.
 * @return
 */
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

/**
 * breezeCheckPermissions
 *
 * Sets and checks different profile related permissions.
 * @return
 */
function breezeCheckPermissions()
{
	global $context, $memberContext, $user_info, $breezeController;

	if (empty($breezeController))
		$breezeController = new BreezeController();

	$tools = $breezeController->get('tools');
	$query = $breezeController->get('query');

	// DUH! winning!
	$context['insert_after_template'] .= Breeze::who(true);

	// Another page already checked the permissions and if the mod is enable, but better be safe...
	if (!$tools->enable('master'))
		redirectexit();

	// If the owner doesn't have any settings don't show the wall, go straight to the static page unless the admin forced it.
	if (empty($context['Breeze']['settings']['owner']) && !$tools->enable('force_enable'))
		redirectexit('action=profile;area=static;u='.$context['member']['id']);

	// If we are forcing the wall, lets check the admin setting first
	elseif ($tools->enable('force_enable'))
		if (!isset($context['Breeze']['settings']['owner']['wall']))
			$context['Breeze']['settings']['owner']['wall'] = 1;

	// Do the normal check, do note this is not an elseif check, its separate.
	else
		if (empty($context['Breeze']['settings']['owner']['wall']))
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

	// Does an ignored user wants to see your wall? never!!!
	if (isset($context['Breeze']['settings']['owner']['kick_ignored']) && !empty($context['Breeze']['settings']['owner']['kick_ignored']) && !empty($context['Breeze']['settings']['owner']['ignoredList']))
	{
		// Make this an array
		$ignored = explode(',', $context['Breeze']['settings']['owner']['ignoredList']);

		if (in_array($user_info['id'], $ignored ))
			redirectexit('action=profile;area=static;u='.$context['member']['id']);
	}

	// I'm sorry, you aren't allowed in here, but here's a nice static page :)
	if (!empty($context['member']['ignore_list']) && is_array($context['member']['ignore_list']) && in_array($user_info['id'], $context['member']['ignore_list']) && !empty($context['member']['options']['kick_ignored']))
		redirectexit('action=profile;area=static;u='.$context['member']['id']);
}
