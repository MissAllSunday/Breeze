<?php

/**
 * Breeze_Query
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

class Breeze_Query
{
	private static $instance;
	private $Main = array();
	private $query = array();
	private $data = array();
	private $query_params = array('rows' =>'*');
	private $query_data = array();
	private $temp = array();
	private $temp2 = array();
	private $valid = false;

	private function __construct()
	{
		Breeze::Load('DB');

		$this->query = array(
			'status' => new Breeze_DB('breeze_status'),
			'comments' => new Breeze_DB('breeze_comments'),
			'user_settings' => new Breeze_DB('breeze_user_settings'),
			'user_settings_modules' => new Breeze_DB('breeze_user_settings_modules'),
			'visit_log' => new Breeze_DB('breeze_visit_log')
		);
	}

	/* Yes, I use a singleton, so what! */
	public static function getInstance()
	{
		if (!self::$instance)
		{
			self::$instance = new Breeze_Query();
		}

		return self::$instance;
	}

	/*
	 * Cleans the old cache value
	 *
	 * Disclaimer: Killing in breeze world means replace the existing cache data with a null value so SMF generates a new cache...
	 * @access private
	 * @param mixed $type the name of value(s) to be deleted
	 * @return void
	 */
	private function KillCache($type)
	{
		if (!is_array($type))
			$type = array($type);

		foreach ($type as $t)
			cache_put_data('Breeze:'. $t, '');
	}

	/*
	 * Return an associative array based on the entered params
	 *
	 * @access private
	 * @param string $table The name of the table to fetch
	 * @param string $row The name of the row to fetch
	 * @param int $value The value to compare to
	 * @return array an associative array
	 */
	private function GetReturn($table, $row, $value)
	{
	}

	private function Query($var)
	{
		return $this->query[$var];
	}

	/*
	 * The main method
	 *
	 * This is the center of breeze, everything in breeze world spins around this method; scary, I know...
	 * @access private
	 * @global $smcFunc the "handling DB stuff" var of SMF
	 * @return array a very big associative array with the ststus ID as key
	 */
	public function Main()
	{
		global $smcFunc;

		/* Use the cache please... */
		if (($this->Main = cache_get_data('Breeze:Main', 120)) == null)
		{
			/* Breeze DB class isn't capable of handling this yet... */
			$result = $smcFunc['db_query']('', '
				SELECT s.status_id, s.status_owner_id, s.status_poster_id, s.status_time, s.status_body, c.comments_id, c.comments_status_id, c.comments_status_owner_id, c.comments_poster_id, c.comments_profile_owner_id, c.comments_time, c.comments_body
				FROM {db_prefix}breeze_status as s
				INNER JOIN {db_prefix}breeze_comments AS c ON (c.comments_status_id = s.status_id)
				ORDER BY status_time DESC
				', ($this->limit ? 'Limit {int:limit}' : '') ,'',
				array(
					'limit' => $this->limit
				)
			);

			/* Populate the array like a boss! */
			while ($row = $smcFunc['db_fetch_assoc']($result))
			{
				$s[$row['status_id']] = array(
					'id' => $row['status_id'],
					'owner_id' => $row['status_owner_id'],
					'poster_id' => $row['status_poster_id'],
					'time' => $row['status_time'],
					'status_body' => $row['status_body']
				);

				/* Comments */
				$s[$row['status_id']]['comments'][$row['comments_id']] = array(
					'id' => $row['comments_id'],
					'poster_id' => $row['comments_poster_id'],
					'owner_id' => $row['comments_profile_owner_id'],
					'time' => $row['comments_time'],
					'body' => $row['comments_body']
				);
			}
		}

		return $this->Main;
	}

	public function InsertStatus($array)
	{
		/* We dont need this anymore */
		$this->KillCache('Main');

		/* Insert! */
		$data = array(
			'status_owner_id' => 'int',
			'status_poster_id' => 'int',
			'status_time' => 'int',
			'status_body' => 'string'
		);

		$indexes = array(
			'status_id'
		);

		$this->Query('status')->InsertData($data, $array, $indexes);
	}

	public function InsertComment($array)
	{
		/* We dont need this anymore */
		$this->KillCache('Main');

		/* Insert! */
		$data = array(
			'comments_status_id' => 'int',
			'comments_status_owner_id' => 'int',
			'comments_poster_id' => 'int',
			'comments_profile_owner_id' => 'int',
			'comments_time' => 'int',
			'comments_body' => 'string'
		);

		$indexes = array(
			'comments_id'
		);

		$this->Query('comments')->InsertData($data, $array, $indexes);
	}

	public function DeleteStatus($id)
	{
		/* We dont need this anymore */
		$this->KillCache('Main');

		/* Delete! */
		$paramsc = array(
			'where' => 'comments_status_id = {int:id}'
		);
		$params = array(
			'where' => 'status_id = {int:id}'
		);

		$data = array(
			'id' => $id
		);

		/* Ladies first */
		$this->Query('comments')->Params($paramsc, $data);
		$this->Query('comments')->DeleteData();
		
		$this->Query('status')->Params($params, $data);
		$this->Query('status')->DeleteData();
	}

	public function DeleteComment($id)
	{
		/* Delete! */
		$params = array(
			'where' => 'comments_id = {int:id}'
		);

		$data = array(
			'id' => $id
		);

		$this->Query('comments')->Params($params, $data);
		$this->Query('comments')->DeleteData();
	}
}