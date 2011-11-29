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
	/* Handle the actions and set the proper files to handle all */
	public function  __construct()
	{
	}

	public static function Wall()
	{
		global $txt, $scripturl, $context, $memID, $memberContext, $modSettings,  $user_info;

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
		$z = $query->data_result;

		/* Append some useful tools */
		foreach (array_keys($z) as $key)
		{
			$z[$key]['breeze_user_info'] = Breeze_UserInfo::Profile($z[$key]['poster_id']);
			$z[$key]['time'] = Breeze_subs::Time_Elapsed($z[$key]['time']);

			/* This isn't very efficient */
			$c_query_params = array(
				'rows' => 'id, status_id, status_owner_id, poster_comment_id, profile_owner_id, time, body',
				'order' => '{raw:sort}',
				'where' => 'status_id={int:memID}'
			);
			$c_query_data = array(
				'sort' => 'id ASC',
				'memID' => $z[$key]['id']
			);
			$c_query = new Breeze_DB('breeze_comment');
			$c_query->Params($c_query_params, $c_query_data);
			$c_query->GetData('id');
			$c = $c_query->data_result;

			/* Yet another for each! */
			foreach(array_keys($c) as $ck)
			{
				$c[$ck]['comment_user_info'] = Breeze_UserInfo::Profile($c[$ck]['poster_comment_id']);
				$c[$ck]['time'] = Breeze_subs::Time_Elapsed($c[$ck]['time']);

				/* Get all the likes for this comment */
			}

			$z[$key]['comments'] = $c;
		}

		$context['member']['status'] = $z;

		/* Done with the status... now its modules time */
		$modules = new Breeze_Modules($context['member']['id']);
		$temp = $modules->GetAllModules();
		$exclude = array('__construct','GetAllModules');
		$context['Breeze']['Modules'] = array();

		foreach($temp as $m)
			if (!in_array($m, $exclude))
				$context['Breeze']['Modules'][$m] = $modules->$m();

		/* Write to the log */
		$log = new Breeze_Logs($context['member']['id']);
		$log->ProfileVisits();

	}
}
