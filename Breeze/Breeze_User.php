<?php

/**
 * @package breeze mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2011 Suki
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

	/* A bunch of wrapper functions so static methods can be callable with a string by SMF */
	function Breeze_Wrapper_Wall(){Breeze_User::Wall();}
	function Breeze_Wrapper_Settings(){Breeze_User::Settings();}
	function Breeze_Wrapper_Permissions(){Breeze_User::Permissions();}

class Breeze_User
{
	public function  __construct()
	{
	}

	public static function Wall()
	{
		global $txt, $scripturl, $context, $memberContext, $modSettings,  $user_info;

		loadLanguage('Breeze');
		loadtemplate('Breeze');
		Breeze::LoadMethod(array(
			'Settings',
			'Subs',
			'Globals',
			'DB',
			'UserInfo',
			'Modules',
			'Logs'
		));
		Breeze_Subs::Headers();

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
		$query_params = array(
			'rows' =>'id, owner_id, poster_id, time, body',
			'order' => '{raw:sort}',
			'where' => 'owner_id={int:memID}'
		);
		$query_data = array(
			'sort' => 'id DESC',
			'memID' => $context['member']['id']
		);
		$query = new Breeze_DB('breeze_status');
		$query->Params($query_params, $query_data);
		$query->GetData('id');
		$z = $query->DataResult();

		/* Append some useful tools */
		foreach (array_keys($z) as $key)
		{
			/* Let's collect the IDs */
			$users_to_load[] = $z[$key]['poster_id'];

			/* Do the conversion from unix time */
			$z[$key]['time'] = Breeze_Subs::Time_Elapsed($z[$key]['time']);

			/* This isn't very efficient */
			$c_query_params = array(
				'rows' => 'id, status_id, status_owner_id, poster_comment_id, profile_owner_id, time, body',
				'order' => '{raw:sort}',
				'where' => 'status_id={int:status_id}'
			);
			$c_query_data = array(
				'sort' => 'id ASC',
				'status_id' => $z[$key]['id']
			);
			$c_query = new Breeze_DB('breeze_comment');
			$c_query->Params($c_query_params, $c_query_data);
			$c_query->GetData('id');
			$c = $c_query->DataResult();

			/* Yet another for each! */
			foreach(array_keys($c) as $ck)
			{
				/* Let's collect the IDs */
				$users_to_load[] = $c[$ck]['poster_comment_id'];

				/* Do the conversion from unix time */
				$c[$ck]['time'] = Breeze_Subs::Time_Elapsed($c[$ck]['time']);

				/* Get all the likes for this comment */
			}

			$z[$key]['comments'] = $c;
		}

		/* Send the array to the template */
		$context['member']['status'] = $z;

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

		/* Done with the status... now it's modules time */
		$modules = new Breeze_Modules($context['member']['id']);
		$temp = $modules->GetAllModules();
		$context['Breeze']['Modules'] = array();

		foreach($temp as $m)
			$context['Breeze']['Modules'][$m] = $modules->$m();

		/* Write to the log */
		$log = new Breeze_Logs($context['member']['id']);
		$log->ProfileVisits();

	}
	
	/* Shows a form for users to set up their wall as needed. */
	function Settings()
	{
		global $context, $user_info;
		
		loadLanguage('Breeze');
		loadtemplate('Breeze');

		/* Is this the right user? */
		if ($context['member']['id'] != $user_info['id'])
			redirectexit('action=profile');
	
		/* Set all the page stuff */
		$context['sub_template'] = 'user_settings';
		$context['can_send_pm'] = allowedTo('pm_send');
		$context['page_title'] = $txt['breeze_user_settings_name'];
		$context['user']['is_owner'] = $context['member']['id'] == $user_info['id'];
		$context['canonical_url'] = $scripturl . '?action=profile;u=' . $context['member']['id'];
	
	}
}