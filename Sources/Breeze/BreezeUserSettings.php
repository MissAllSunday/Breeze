<?php

/**
 * BreezeUserSettings
 *
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0 Beta 2 Beta 1
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

class BreezeUserSettings
{
	protected $_data = array();

	function __construct($user)
	{
		if (empty($user))
			return false;

		$this->_user = $user;
		$this->_data = Breeze::query()->getUserSettings($this->_user);
	}

	public function getUserSettings()
	{
		if (!empty($this->_data))
		{
			if (!empty($this->_data['wall_settings']))
				$this->_data['wall_settings'] = json_decode($this->_data['wall_settings'] ,true);

			if (!empty($this->_data['pm_ignore_list']))
				$this->_data['pm_ignore_list'] = explode(',', $this->_data['pm_ignore_list']);

			return $this->_data;
		}

		else
			return false;
	}

	public function updateUserSettings($save_data)
	{
		if (!empty($save_data['wall_settings']))
			$save_data['wall_settings'] = json_encode($save_data['wall_settings']);

		updateMemberData($this->_user, $save_data);
	}

	public function insertUserSettings($save_data)
	{
		if (!empty($save_data['wall_settings']))
			$save_data['wall_settings'] = json_encode($save_data['wall_settings']);

		Breeze::query()->insertUserSettings($this->_user, $save_data);
	}
}