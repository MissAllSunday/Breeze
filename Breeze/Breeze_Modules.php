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

class Breeze_Modules
{
	private $user_settings;
	private $id;

	public function __construct($id)
	{
		$this->id = $id;
		Breeze::Load(array(
			'UserInfo',
			'DB',
			'Logs',
			'Subs',
			'UserSettings'
		));
		
		loadLanguage('Breeze');
		loadtemplate('Breeze');

		$this->user_settings = new Breeze_UserSettings();
		$this->user_settings->Load_UserSettings($this->id);
	}

	public function GetAllModules()
	{
		$temp = get_class_methods('Breeze_Modules');
		$temp = Breeze_Subs::Remove($temp, array(
			$this->user_settings->enable('enable_buddies') ? '' : 'enable_buddies',
			$this->user_settings->enable('enable_visitors') ? '' : 'enable_visitors',
			'__construct',
			'GetAllModules'
		), false);

		return $temp;
	}

	public function enable_buddies()
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

	function enable_visitors()
	{
		global $context, $txt;

		$return = '';
		$logs = new Breeze_logs($this->id);
		$temp = $logs->GetProfileVisits();
		$columns = 3;
		$counter = 0;
		$array['title'] = $txt['breeze_modules_enable_visitors_title'];

		$array['data'] = $txt['breeze_modules_enable_visitors_description'] .'<table><tr>';

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