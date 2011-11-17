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
		global $txt, $scripturl, $context, $memID, $memberContext;

		loadLanguage('Breeze');
		loadtemplate('Breeze');
		LoadBreezeMethod(array(
			'Breeze_Settings',
			'Breeze_Subs',
			'Breeze_Globals',
			'Breeze_DB',
			'Breeze_UserInfo'
		));
		Breeze_Subs::Headers();

		/* Set all the page stuff */
		$context['page_title'] = $txt['breeze_general_wall'];
		$context['sub_template'] = 'user_wall';

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
				}

				$z[$key]['comments'] = $c;
			}

			$context['member']['status'] = $z;
	}
}
