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

	/* This file gets the data from a form via Breeze_Ajax, do all the things that needs to be done and returns the formatted data  */
class Breeze_Data
{
	private static $instances = array();
	public static $done = false;

	function __construct($type)
	{
		if ($type == 'status')
			$this->type = 'status';
		elseif ($type == 'comment')
			$this->type = 'comment';
		else
			return;

		$this->done = false;
	}

	public static function factory($type)
	{
		if (array_key_exists($type, self::$instances))
		{
			return self::$instances[$type];
		}
		return self::$instances[$type] = new Breeze_Data($type);
	}

	public function Check($value)
	{
		$pattern = '/error_/';

		if (preg_match($pattern, $value))
			return false;

		else
			return true;

	}

	/* Just send the data to the database */
	public static function Record($params)
	{
		Breeze::LoadMethod(array('DB', 'Globals'));

		$data = array();
		$values = array();
		$indexes = array();
		$content = isset($params['body']) ? $params['body'] : '';

		if ($params['type'] == 'status')
		{
			/* Insert! */
			$data = array(
				'owner_id' => 'int',
				'poster_id' => 'int',
				'time' => 'int',
				'body' => 'string'
			);
			$values = array(
				$params['owner_id'],
				$params['poster_id'],
				time(),
				$content
			);
			$indexes = array(
				'id'
			);
			$insert = new Breeze_DB('breeze_'.$params['type']);
			$insert->InsertData($data, $values, $indexes);
		}

		else
		{
			/* Insert! */
			$data = array(
				'status_id' => 'int',
				'status_owner_id' => 'int',
				'poster_comment_id' => 'int',
				'profile_owner_id' => 'int',
				'time' => 'int',
				'body' => 'string'
			);
			$values = array(
				$params['status_id'],
				$params['status_owner_id'],
				$params['poster_comment_id'],
				$params['profile_owner_id'],
				time(),
				$content
			);
			$indexes = array(
				'id'
			);
			$insert = new Breeze_DB('breeze_'.$params['type']);
			$insert->InsertData($data, $values, $indexes);
		}
	}

	/* Record this please... */
	public static function Log($params)
	{
	}

	/* Temp, this function will send out notifications or private messages or both! to the user if some other person posted on his/her wall */
	public static function Notifications($params)
	{
	}
}

