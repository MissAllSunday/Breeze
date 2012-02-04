<?php

/**
 * Breeze_
 *
 * The purpose of this file is To show the user wall and provide a settings page
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011, Jessica González
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
 * Portions created by the Initial Developer are Copyright (C) 2011
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
	function Breeze_Wrapper_Permissions(){Breeze_User::Permissions();}
	function Breeze_Wrapper_Modules(){Breeze_User::Modules();}

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
			'Globals',
			'UserInfo',
			'Modules',
			'Logs',
			'Query'
		));

		$settings = Breeze_Settings::getInstance();
		$query = Breeze_Query::getInstance();
		$tools = new Breeze_Subs();

		/* Another page already checked the permissions and if the mod is enable, but better be safe... */
		if ($settings->Enable('admin_settings_enable'))
			redirectexit();

		/* Load this user's settings */
		$user_settings = $query->GetUserSettings($context['member']['id']);

		/* Does the user even enable this? */
		if ($user_settings['enable_wall'] == 0 || !allowedTo('profile_view_own') || !allowedTo('profile_view_any'))
			redirectexit('action=profile;area=static;u='.$context['member']['id']);

		/* Get this user's ignore list */
		if (empty($context['member']['ignore_list']))
			$context['member']['ignore_list'] = $query->GetUserIgnoreList($context['member']['id']);

		/* I'm sorry, you aren't allowed in here, but here's a nice static page :) */
		if (in_array($user_info['id'], $context['member']['ignore_list']))
			redirectexit('action=profile;area=static;u='.$context['member']['id']);

		/* Display all the JavaScript bits */
		$tools->Headers();

		/* Set all the page stuff */
		$context['sub_template'] = 'user_wall';
		$context['can_send_pm'] = allowedTo('pm_send');
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

		/* Send the array to the template */
		$context['member']['status'] = $status;

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

		/* Write to the log */
		$query->WriteProfileVisit($context['member']['id'], $user_info['id']);
	}

	/* Shows a form for users to set up their wall as needed. */
	function Settings()
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

		/* Load all we need */
		$query = Breeze_Query::getInstance();
		$text = Breeze_Settings::getInstance();
		$data = $query->GetSettingsByUser($context['member']['id']);

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

		$form->AddHr();

		$form->AddSubmitButton('save');

		/* Send the form to the template */
		$context['Breeze']['UserSettings']['Form'] = $form->Display();

		/* Saving? */
		if (isset($_GET['save']))
		{
			$temp = $form->ReturnElementNames();
			$save_data = array();
			$save_data['user_id'] = $context['member']['id'];

			foreach ($temp as &$type)
				$save_data[$type] = !empty($_POST[$type]) ? 1 : 0;

			/* If the data already exist, update... */
			if ($already == true)
				$query->UpdateUserSettings($save_data);

			/* ...if not, insert. */
			else
				$query->InsertUserSettings($save_data);

			redirectexit('action=profile;area=breezesettings;u='.$context['member']['id']);
		}
	}
}