<?php

/**
 * Breeze_
 * 
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0
 * @author Jessica Gonzalez <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011, Jessica Gonzalez
 * @license http://mozilla.org/MPL/2.0/
 */

/**
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License version 2.0 (the \License"). You can obtain a copy of the
 * License at http://mozilla.org/MPL/2.0/.
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Breeze_Modules
{
	public function __construct($id)
	{
		$this->id = $id;
		Breeze::LoadMethod(array('UserInfo','DB', 'Logs', 'Subs'));
	}

	public function GetAllModules()
	{
		$temp = get_class_methods('Breeze_Modules');
		$temp = self::remove($temp, '__construct', false);
		$temp = self::remove($temp, 'GetAllModules', false);
		$temp = self::remove($temp, 'remove', false);

		return $temp;
	}

	public function GetBuddies()
	{
		global $context;

		$query_params = array(
			'rows' =>'buddy_list',
			'where' => 'id_member={int:id_member}',
			'limit' => 1
		);
		$query_data = array(
			'id_member' => $this->id
		);

		$query = new Breeze_DB('members');
		$query->Params($query_params, $query_data);
		$query->GetData(null, true);
		$temp = $query->DataResult();
		$temp2 = explode(',', $temp['buddy_list']);
		$columns = 3;
		$counter = 0;
		$array['title'] = 'Buddies';

		if (!empty($temp['buddy_list']))
		{
			$array['data'] = '<table><tr>';

			foreach($temp2 as $t)
			{
				$context['Breeze']['user_info'][$t] = Breeze_UserInfo::Profile($t, true);

				$array['data'] .= '<td> '.$context['Breeze']['user_info'][$t].' </td>';

				if ($counter % $columns == 0)
					$array['data'] .= '</tr><tr>';

				$counter++;
			}
			$array['data'] .= '</tr></table>';
		}

		return $array;
	}

	function GetVisits()
	{
		global $context;

		$return = '';
		$logs = new Breeze_logs($this->id);
		$temp = $logs->GetProfileVisits();
		$columns = 3;
		$counter = 0;
		$array['title'] = 'Latest Visitors';

		$array['data'] = '<table><tr>';

		if (!empty($temp))
			foreach($temp as $t)
			{
				$context['Breeze']['user_info'][$t['user']] = Breeze_UserInfo::Profile($t['user'], true);

				$array['data'] .= '<td>'.$context['Breeze']['user_info'][$t['user']].'<br />'.timeformat($t['time']).'</td>';

				if ($counter % $columns == 0)
					$array['data'] .= '</tr><tr>';

				$counter++;
			}

		$array['data'] .= '</tr></table>';

		return $array;
	}
	
	/* Shows the latest activity */
	function Activity()
	{
		global $context;

		$array['title'] = 'Activity';

		$logs = new Breeze_Logs($this->id);
		$temp = $logs->GetLatestStatus();
		$poster = $context['Breeze']['user_info']['link'][$this->id];

		$array['data'] = '<ul class="breeze_user_left_info" style="min-height:120px">';

		foreach($temp as $t)
		{
			$profile_owner = $context['Breeze']['user_info']['link'][$t['owner_id']];
			$array['data'] .= '<li>'.$poster. ' Commented in '.$profile_owner.'\' s profile '.Breeze_Subs::Time_Elapsed($t['time']).'<br /></li>';

		}

		$array['data'] .= '</ul>';


		return $array;
	}
}