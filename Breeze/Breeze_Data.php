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
	
	private static function preparse($s)
	{
		$s = strtr($s, array('[]' => '&#91;]', '[&#039;' => '&#91;&#039;'));
		$s = preg_replace('~&amp;#(\d{4,5}|[2-9]\d{2,4}|1[2-9]\d);~', '&#$1;', $s);
		$s = preg_replace('~\[nobbc\](.+?)\[/nobbc\]~ie', '\'[nobbc]\' . strtr(\'$1\', array(\'[\' => \'&#91;\', \']\' => \'&#93;\', \':\' => \'&#58;\', \'@\' => \'&#64;\')) . \'[/nobbc]\'', $s);
		$s = strtr($s, array("\r" => ''));
		$s = preg_replace('~\.{100,}~', '...', $s);
	}
}