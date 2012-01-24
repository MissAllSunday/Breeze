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
			$this->type = 'comments';
		else
			return;

		$this->done = false;
	}

	public function factory($type)
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
	public function Record($params)
	{
		Breeze::LoadMethod(array(
			'DB',
			'Globals'
		));

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
			$insert = new Breeze_DB('breeze_comments');
			$insert->InsertData($data, $values, $indexes);
		}
	}
	
	public function Notifications($type, $who, $on)
	{
	
	}

}