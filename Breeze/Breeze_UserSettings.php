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

class Breeze_UserSettings
{
	private $data = array();
	private static $already = false;

	function __construct()
	{
		global $context;

		if (isset($context['Breeze']['UserSettings'][$context['member']['id']]) && !empty($context['Breeze']['UserSettings'][$context['member']['id']]))
		{
			$this->data = $context['Breeze']['UserSettings'][$context['member']['id']];
			self::$already = true;
		}
	}

	function Current_UserSettings()
	{
		global $context;

		if (!self::$already)
		{
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
			$this->data = $query->DataResult();

			if (!empty($data))
				$context['Breeze']['UserSettings'][$context['member']['id']] = $this->data;
		}
	}

	function Load_UserSettings($user)
	{
		global $context;

		if (!self::$already)
		{
			/* Load the user settings */
			$query_params = array(
				'rows' =>'*',
				'where' => 'user_id={int:user_id}',
			);
			$query_data = array(
				'user_id' => $user,
			);
			$query = new Breeze_DB('breeze_user_settings');
			$query->Params($query_params, $query_data);
			$query->GetData(null, true);
			$this->data = $query->DataResult();

			if (!empty($data))
				$context['Breeze']['UserSettings'][$user] = $this->data;
		}
	}

	function enable($setting)
	{
		if (!empty($this->data[$setting]))
			return true;
		else
			return false;
	}

	function setting($setting)
	{
		if (!empty($this->data[$setting]))
			return $this->data[$setting];
		else
			return false;
	}
}