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
	public $Main = array();
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

	private function UnsetTemp()
	{
		unset($this->temp);
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
	private function GetReturn($row, $value)
	{
		/* Get the data */
		$this->temp = $this->Main();

		/* Needs to be empty by default */
		$this->r = array();

		/* Do this only if there is something to work with */
		if ($this->temp)
		{
			/* Generate an array with a defined key */
			foreach($this->temp as $t)
				if ($t[$row] == $value)
					$this->r[] = $t;
		}

		/* Clean */
		$this->UnsetTemp();

		/* Return the info we want as we want it */
		return $this->r;
	}

	private function GetSingleValue($type, $value)
	{
		/* Get the data */
		$this->temp = $this->Main();

		if ($type == 'status')
			foreach($this->temp as $s)
				if ($s == $value)
					$this->r = $s;

		elseif ($type == 'comment')
			foreach($this->temp as $c)
				if ($c == $value)
					$this->r = $c;

		$this->UnsetTemp();

		return $this->r;
	}

	public function GetSingleStatus()
	{
		/* Get the value directly from the DB */
		$this->query_params = array(
			'rows' => 'status_id',
			'order' => '{raw:sort}',
			'limit' => '{int:limit}'
		);

		$this->query_data = array(
			'sort' => 'status_id DESC',
			'limit' => 1
		);
		$this->Query('status')->Params($this->query_params, $this->query_data);
		$this->Query('status')->GetData(null, true);

		/* Done? */
		return $this->Query('status')->DataResult();
	}
	
	public function GetSingleComment()
	{
		/* Get the value directly from the DB */
		$this->query_params = array(
			'rows' => 'comments_id',
			'order' => '{raw:sort}',
			'limit' => '{int:limit}'
		);

		$this->query_data = array(
			'sort' => 'comments_id DESC',
			'limit' => 1
		);
		$this->Query('comments')->Params($this->query_params, $this->query_data);
		$this->Query('comments')->GetData(null, true);

		/* Done? */
		return $this->Query('comments')->DataResult();
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
	private function Main()
	{
		global $smcFunc;

		Breeze::Load(array(
			'Subs',
			'UserInfo',
			'Parser',
		));

		$tools = new Breeze_Subs();
		$parser = new Breeze_Parser();

		/* Use the cache please... */
		if (($this->Main = cache_get_data('Breeze:Main', 120)) == null)
		{
			/* Breeze DB class isn't capable of handling this yet... */
			$result = $smcFunc['db_query']('', '
				SELECT s.status_id, s.status_owner_id, s.status_poster_id, s.status_time, s.status_body, c.comments_id, c.comments_status_id, c.comments_status_owner_id, c.comments_poster_id, c.comments_profile_owner_id, c.comments_time, c.comments_body
				FROM {db_prefix}breeze_status as s
				LEFT JOIN {db_prefix}breeze_comments AS c ON (c.comments_status_id = s.status_id)
				ORDER BY status_time DESC
				',
				array()
			);

			/* Populate the array like a boss! */
			while ($row = $smcFunc['db_fetch_assoc']($result))
			{
				$this->Main[$row['status_id']] = array(
					'id' => $row['status_id'],
					'owner_id' => $row['status_owner_id'],
					'poster_id' => $row['status_poster_id'],
					'time' => $tools->TimeElapsed($row['status_time']),
					'body' => $parser->Display($row['status_body'])
				);

				/* Comments */
				if ($row['comments_id'])
				{
					$this->Main[$row['status_id']]['comments'][$row['comments_id']] = array(
						'id' => $row['comments_id'],
						'poster_id' => $row['comments_poster_id'],
						'owner_id' => $row['comments_profile_owner_id'],
						'time' => $tools->TimeElapsed($row['comments_time']),
						'body' => $parser->Display($row['comments_body'])
					);
				}
				else
					$this->Main[$row['status_id']]['comments'] = array();
			}
		}

		return $this->Main;
	}

	public function GetStatusByProfile($id)
	{
		return $this->GetReturn('owner_id', $id);
	}

	public function GetStatus()
	{
		return $this->Main();
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