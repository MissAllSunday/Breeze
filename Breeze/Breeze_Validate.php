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

class Breeze_Validate
{
	private static $instance;

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

	public function Get($type)
	{
		$return = '';

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