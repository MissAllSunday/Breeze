<?php

/**
 * BreezeModules
 *
 * The purpose of this file is to show all the current, enable modules for the users.
 * @package Breeze mod
 * @version 1.0 Beta 2
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2012, Jessica González
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
	die('Hacking attempt...');

class BreezeModules
{
	private $user_settings;
	private $profile_id;
	private $text;
	private $query;

	public function __construct($id)
	{
		/* Set some things */
		$this->profile_id = $id;
		$this->text = BreezeSettings::getInstance();
		$this->query = BreezeQuery::getInstance();
		$this->user_settings = $this->query->GetUserSettings($this->profile_id);
	}

	public function GetAllModules()
	{
		/* This is fugly, I need to find a better way to handle modules, maybe a separate folder? */
		$array = array();
		$temp = get_class_methods('BreezeModules');
		$temp = BreezeSubs::Remove($temp, array(
			$this->user_settings['enable_visits_module'] ? '' : 'enable_visits_module',
			'__construct',
			'GetAllModules'
		), false);

		foreach ($temp as $k => $t)
			$array[$t] =  $this->$t();

		return $array;
	}

	function enable_visits_module()
	{
		/* Set this as empty */
		$array = array(
			'title' => $this->text->getText('modules_enable_visitors_title'),
			'data' => ''
		);

		/* Get the last visits to this profile page */
		$visits = $this->query->GetProfilevisits($this->profile_id, $this->user_settings['visits_module_timeframe']);
		$columns = 3;
		$counter = 0;

		if (!empty($visits))
		{
			$array['data'] = '<table><tr>';

			foreach($visits as $t)
			{
				$context['Breeze']['user_info'][$t['user']] = BreezeUserInfo::Profile($t['user'], true);

				if ($counter % $columns == 0)
					$array['data'] .= '</tr><tr>';

				$array['data'] .= '<td>'.$context['Breeze']['user_info'][$t['user']].'<br /><pan style="text-align: center;" class="smalltext">'. timeformat($t['time']) .'</span></td>';

				$counter++;
			}

			$array['data'] .= '</tr></table>';
		}

		return $array;
	}
}