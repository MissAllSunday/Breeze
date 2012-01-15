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

class Breeze_Validate
{
	private static $instance;
	private $return = '';

	private function __construct()
	{
		Breeze::LoadMethod('DB');
	}

	public static function getInstance()
	{
		if (!self::$instance)
		 {
			self::$instance = new Breeze_Validate();
		}
		return self::$instance;
	}

	public function Get($type, $key = false)
	{
		if (empty($type))
			return $return;

		else
		{
			$query_params = array(
				'rows' =>'id'
			);

			$query = new Breeze_DB('breeze_'.$type);
			$query->Params($query_params);
			$query->GetData('id');

			if (!empty($query->data_result))
				$return = $query->data_result;

			return $return;
		}
	}
}