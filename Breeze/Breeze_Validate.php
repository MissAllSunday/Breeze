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

class Breeze_Validate
{
	private static $instance;

	private function __construct()
	{
		LoadBreezeMethod('Breeze_DB');
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
