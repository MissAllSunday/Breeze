<?php

/**
 * Breeze_
 *
 * The purpose of this file is
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
	static $already = false;

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


		echo '<pre>';
		print_r($query);
		echo '</pre>';

		/* Collect the IDs to build their profile's lightbox */
		foreach($status as $s)
		{
			$users_to_load[] = $s['poster_id'];

			/* Comments too */
			if ($s['comments'])
				foreach($s['comments'] as $c)
					$users_to_load[] = $c['poster_id'];
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

		/* Done with the status... now it's modules time */
/* 		$modules = new Breeze_Modules($context['member']['id']);
		$temp = $modules->GetAllModules();
		$context['Breeze']['Modules'] = array();

		foreach($temp as $m)
			$context['Breeze']['Modules'][$m] = $modules->$m(); */

		/* Write to the log */
/* 		$log = new Breeze_Logs($context['member']['id']);
		$log->ProfileVisits(); */

	}

	/* Shows a form for users to set up their wall as needed. */
	function Settings()
	{
		global $context, $user_info, $txt, $scripturl;

		loadtemplate('Breeze');
		loadLanguage('Breeze');
		Breeze::Load(array(
			'Form',
			'Globals',
			'DB'
		));

		/* Is this the right user? */
		if ($context['member']['id'] != $user_info['id'])
			redirectexit('action=profile');

		/* Set all the page stuff */
		$context['sub_template'] = 'user_settings';
		$context['can_send_pm'] = allowedTo('pm_send');
		$context['page_title'] = $txt['breeze_user_settings_name'];
		$context['user']['is_owner'] = $context['member']['id'] == $user_info['id'];
		$context['canonical_url'] = $scripturl . '?action=profile;u=' . $context['member']['id'];

		/* Load the user settings */
		$query_params = array(
			'rows' =>'*',
			'where' => 'user_id={int:user_id}',
		);
		$query_data = array(
			'user_id' => $context['member']['id'],
		);
		$query = new Breeze_DB('breeze_user_settings');
		$query->Params($query_params, $query_data);
		$query->GetData(null, true);
		$data = $query->DataResult();

		if (!empty($data))
			self::$already = true;

		$FormData = array(
			'action' => 'profile;area=breezesettings;save;u='.$context['member']['id'],
			'method' => 'post',
			'id_css' => 'user_settings_form',
			'name' => 'user_settings_form',
			'class_css' => 'user_settings_form',
			'onsubmit' => '',
		);

		/* The long, long form */
		$form = new Breeze_Form($FormData);
		$form->AddCheckBox('enable_notification_pm', 1, array(
			'notification_pm',
			'notification_pm_sub'
		), !empty($data['enable_notification']) ? true : false);

		$form->AddCheckBox('enable_notification_wall', 1, array(
			'notification_wall',
			'notification_wall'
		), !empty($data['enable_notification_wall']) ? true : false);

		$form->AddSubmitButton('save');

		/* Send the form to the template */
		$context['Breeze']['UserSettings']['Form'] = $form->Display();

		/* Saving? */
		if (isset($_GET['save']))
		{
			$sa = Breeze_Globals::factory('post');

			$enable_buddies = $sa->raw('enable_buddies') ? 1 : 0;
			$enable_visitors = $sa->raw('enable_visitors') ? 1 : 0;
			$enable_notification = $sa->raw('enable_notification') ? 1 : 0;

			/* If the data already exist, update... */
			if (self::$already == true)
			{
				$params = array(
					'set' =>'enable_buddies={int:enable_buddies}, enable_visitors={int:enable_visitors}, enable_notification={int:enable_notification}',
					'where' => 'user_id = {int:user_id}',
				);

				$data = array(
					'enable_buddies' => $enable_buddies,
					'enable_visitors' => $enable_visitors,
					'enable_notification' => $enable_notification,
					'user_id' => $context['member']['id']
				);

				$updatedata = new Breeze_DB('breeze_user_settings');
				$updatedata->Params($params, $data);
				$updatedata->UpdateData();
			}

			/* ...if not, insert. */
			else
			{
				$data = array(
					'enable_buddies' => 'int',
					'enable_visitors' => 'int',
					'enable_notification' => 'int',
					'user_id' => 'int'
				);
				$values = array(
					$enable_buddies,
					$enable_visitors,
					$enable_notification,
					$context['member']['id']
				);
				$indexes = array(
					'user_id'
				);
				$insert = new Breeze_DB('breeze_user_settings');
				$insert->InsertData($data, $values, $indexes);
			}

			redirectexit('action=profile;area=breezesettings;u='.$context['member']['id']);
		}
	}

	function Permissions()
	{
		global $context, $user_info, $txt, $scripturl;

		loadtemplate('Breeze');
		loadLanguage('Breeze');
		Breeze::Load(array(
			'Form',
			'Globals',
			'DB'
		));

		/* Is this the right user? */
		if ($context['member']['id'] != $user_info['id'])
			redirectexit('action=profile');

		/* Set all the page stuff */
		$context['sub_template'] = 'user_settings';
		$context['page_title'] = $txt['breeze_general_my_wall_modules'];
		$context['user']['is_owner'] = $context['member']['id'] == $user_info['id'];
		$context['canonical_url'] = $scripturl . '?action=profile;u=' . $context['member']['id'];

		/* Load the user settings */
		$query_params = array(
			'rows' =>'*',
			'where' => 'user_id={int:user_id}',
		);
		$query_data = array(
			'user_id' => $context['member']['id'],
		);
		$query = new Breeze_DB('breeze_user_settings_modules');
		$query->Params($query_params, $query_data);
		$query->GetData(null, true);
		$data = $query->DataResult();

		if (!empty($data))
			self::$already = true;

		$FormData = array(
			'action' => 'profile;area=breezepermissions;save;u='.$context['member']['id'],
			'method' => 'post',
			'id_css' => 'user_settings_form',
			'name' => 'user_settings_form',
			'class_css' => 'user_settings_form',
			'onsubmit' => '',
		);

		/* The long, long permissions form */
		$form = new Breeze_Form($FormData);


		$form->AddSubmitButton('save');

		/* Send the form to the template */
		$context['Breeze']['UserSettings']['Form'] = $form->Display();

		/* Saving? */
		if (isset($_GET['save']))
		{
			$sa = Breeze_Globals::factory('post');

			$enable_buddies = $sa->raw('enable_buddies') ? 1 : 0;
			$enable_visitors = $sa->raw('enable_visitors') ? 1 : 0;
			$enable_notification = $sa->raw('enable_notification') ? 1 : 0;

			/* If the data already exist, update... */
			if (self::$already == true)
			{
				$params = array(
					'set' =>'enable_buddies={int:enable_buddies}, enable_visitors={int:enable_visitors}, enable_notification={int:enable_notification}',
					'where' => 'user_id = {int:user_id}',
				);

				$data = array(
					'enable_buddies' => $enable_buddies,
					'enable_visitors' => $enable_visitors,
					'enable_notification' => $enable_notification,
					'user_id' => $context['member']['id']
				);

				$updatedata = new Breeze_DB('breeze_user_settings_permissions');
				$updatedata->Params($params, $data);
				$updatedata->UpdateData();
			}

			/* ...if not, insert. */
			else
			{
				$data = array(
					'enable_buddies' => 'int',
					'enable_visitors' => 'int',
					'enable_notification' => 'int',
					'user_id' => 'int'
				);
				$values = array(
					$enable_buddies,
					$enable_visitors,
					$enable_notification,
					$context['member']['id']
				);
				$indexes = array(
					'user_id'
				);
				$insert = new Breeze_DB('breeze_user_settings_permissions');
				$insert->InsertData($data, $values, $indexes);
			}

			redirectexit('action=profile;area=breezepermissions;u='.$context['member']['id']);
		}
	}

	function Modules()
	{
		global $context, $user_info, $txt, $scripturl;

		loadtemplate('Breeze');
		loadLanguage('Breeze');
		Breeze::Load(array(
			'Form',
			'Globals',
			'DB'
		));

		/* Is this the right user? */
		if ($context['member']['id'] != $user_info['id'])
			redirectexit('action=profile');

		/* Set all the page stuff */
		$context['sub_template'] = 'user_settings';
		$context['page_title'] = $txt['breeze_general_my_wall_modules'];
		$context['user']['is_owner'] = $context['member']['id'] == $user_info['id'];
		$context['canonical_url'] = $scripturl . '?action=profile;u=' . $context['member']['id'];

		/* Load the user settings */
		$query_params = array(
			'rows' =>'*',
			'where' => 'user_id={int:user_id}',
		);
		$query_data = array(
			'user_id' => $context['member']['id'],
		);
		$query = new Breeze_DB('breeze_user_settings_modules');
		$query->Params($query_params, $query_data);
		$query->GetData(null, true);
		$data = $query->DataResult();

		if (!empty($data))
			self::$already = true;

		$FormData = array(
			'action' => 'profile;area=breezemodules;save;u='.$context['member']['id'],
			'method' => 'post',
			'id_css' => 'user_settings_form',
			'name' => 'user_settings_form',
			'class_css' => 'user_settings_form',
			'onsubmit' => '',
		);

		/* The long, long Modules form */
		$form = new Breeze_Form($FormData);

		$form->AddCheckBox('enable_buddies', 1, array(
			'enable_buddies',
			'enable_buddies_sub'
		), !empty($data['enable_buddies']) ? true : false);

		$form->AddCheckBox('show_avatar_buddies', 1, array(
			'show_avatar',
			'show_avatar_sub'
		), !empty($data['show_avatar_buddies']) ? true : false);

		$form->AddText('how_many_buddies', !empty($data['how_many_buddies']) ? $data['how_many_buddies'] : '', array(
			'how_many_buddies',
			'how_many_buddies_sub'
		), 6, 6);

		$form->AddHr();

		$form->AddCheckBox('enable_visitors', 1, array(
			'visitors',
			'visitors_sub'
		), !empty($data['enable_visitors']) ? true : false);

		$form->AddText('how_many_visitors', !empty($data['how_many_visitors']) ? $data['how_many_visitors'] : '', array(
			'how_many_visitors',
			'how_many_visitors_sub'
		), 6, 6);

		if (self::$already)
			$form->AddSelect('time_frame', array(
				'time_frame',
				'time_frame_sub'
			), $values = array(
					'hour' => array(
						'time_hour',
						$data['time_frame'] == 'hour' ? 'selected' : false
					),
					'day' => array(
						'time_day',
						$data['time_frame'] == 'day' ? 'selected' : false
					),
					'week' => array(
						'time_week',
						$data['time_frame'] == 'week' ? 'selected' : false
					),
					'month' => array(
						'time_month',
						$data['time_frame'] == 'month' ? 'selected' : false
					),
				));

		else
			$form->AddSelect('time_frame', array(
				'time_frame',
				'time_frame_sub'
			), $values = array(
					'hour' => array(
						'time_hour',
						false
					),
					'day' => array(
						'time_day',
						false
					),
					'week' => array(
						'time_week',
						'selected'
					),
					'month' => array(
						'time_month',
						false
					),
				));


		$form->AddCheckBox('enable_show_avatar', 1, array(
			'show_avatar',
			'show_avatar_sub'
		), !empty($data['enable_show_avatar']) ? true : false);

		$form->AddSubmitButton('save');

		/* Send the form to the template */
		$context['Breeze']['UserSettings']['Form'] = $form->Display();

		/* Saving? */
		if (isset($_GET['save']))
		{
			$sa = Breeze_Globals::factory('post');

			$enable_buddies = $sa->raw('enable_buddies') ? 1 : 0;
			$enable_visitors = $sa->raw('enable_visitors') ? 1 : 0;
			$enable_notification = $sa->raw('enable_notification') ? 1 : 0;

			/* If the data already exist, update... */
			if (self::$already == true)
			{
				$params = array(
					'set' =>'enable_buddies={int:enable_buddies}, enable_visitors={int:enable_visitors}, enable_notification={int:enable_notification}',
					'where' => 'user_id = {int:user_id}',
				);

				$data = array(
					'enable_buddies' => $enable_buddies,
					'enable_visitors' => $enable_visitors,
					'enable_notification' => $enable_notification,
					'user_id' => $context['member']['id']
				);

				$updatedata = new Breeze_DB('breeze_user_settings_modules');
				$updatedata->Params($params, $data);
				$updatedata->UpdateData();
			}

			/* ...if not, insert. */
			else
			{
				$data = array(
					'enable_buddies' => 'int',
					'enable_visitors' => 'int',
					'enable_notification' => 'int',
					'user_id' => 'int'
				);
				$values = array(
					$enable_buddies,
					$enable_visitors,
					$enable_notification,
					$context['member']['id']
				);
				$indexes = array(
					'user_id'
				);
				$insert = new Breeze_DB('breeze_user_settings_modules');
				$insert->InsertData($data, $values, $indexes);
			}

			redirectexit('action=profile;area=breezemodules;u='.$context['member']['id']);
		}
	}
}