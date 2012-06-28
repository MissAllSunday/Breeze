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
		$this->_data = $this->loadUserSettings();
	}

	public function loadUserSettings()
	{
		$return = Breeze::query()->getUserSettings($this->_user);

		if (!empty($return))
		{
			if (!empty($return['wall_settings']))
			{
				$return += json_decode($return['wall_settings'] ,true);

				unset($return['wall_settings']);
			}

			if (!empty($return['pm_ignore_list']))
				$return['pm_ignore_list'] = explode(',', $return['pm_ignore_list']);

			return $return;
		}

		else
			return false;
	}

	public function getUserSettings()
	{
		return $this->_data;
	}

	public function getUserIgnoreList()
	{
		return $this->_data['pm_ignore_list'];
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

	public function enable ($var)
	{
		if (!empty($this->_data[$var]))
			return true;

		else
			return false;
	}

	public function getSetting($var)
	{
		if (!empty($this->_data[$var]))
			return $this->_data[$var];

		else
			return false;
	}
}