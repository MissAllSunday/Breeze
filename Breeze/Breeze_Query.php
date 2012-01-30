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
	protected $Status = array();
	protected $Comments = array();
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
	 * Set the temp array back to null
	 *
	 * @access private
	 * @return void
	 */
	private function ResetTemp()
	{
		$this->temp = array();
	}

	/*
	 * Set the return array back to null
	 *
	 * @access private
	 * @return void
	 */
	private function ResetReturn()
	{
		$this->r = array();
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
	private function GetReturn($type, $row, $value)
	{
		/* Get the data */
		$this->SwitchData($type);

		/* Needs to be empty by default */
		$this->ResetReturn();

		/* Do this only if there is something to work with */
		if ($this->temp)
		{
			/* Generate an array with a defined key */
			foreach($this->temp as $t)
				if ($t[$row] == $value)
					$this->r[] = $t;
		}

		/* Clean the Temp array */
		$this->ResetTemp();

		/* Return the info we want as we want it */
		return $this->r;
	}

	/*
	 * Set the temp array with the correct data acording to the type specified
	 *
	 * This make it easy to add more types if we ever need more types.
	 * @param string the data type
	 * @access private
	 * @return void
	 */
	private function SwitchData($type)
	{
		switch ($type)
		{
			case 'status':
				$this->temp = $this->Status();
				break;
			case 'comments':
				$this->temp = $this->Comments();
				break;
			case 'likes':
				$this->temp = $this->Likes();
				break;
			case 'logs':
				$this->temp = $this->Logs();
				break;
		}
	}

	/*
	 * Get a single value from an specified array.
	 *
	 * Needs a type, a row and a value, this iterates X array looking for X value in X row. This is a generic method used by other more specific methods
	 * @param string $type the data type
	 * @param mixed $value  Most of the cases will be a int.
	 * @access private
	 * @return array an array with the requested data
	 */
	private function GetSingleValue($type, $value)
	{
		/* Cleaning */
		$this->ResetTemp();
		$this->ResetReturn();

		/* Get the data */
		$this->SwitchData($type);

		foreach($this->temp as $t)
			if ($t == $value)
				$this->r = $t;

		/* Cleaning */
		$this->ResetTemp();

		return $this->r;
	}

	/*
	 * Queries the DB directly to get the last status added.
	 *
	 * It is not reliable to use the cache array for this one so let's do a query here. We will only fetch the ID because that is the only thing we want. Mostly used for the server response in class Breeze_Ajax.
	 * @access public
	 * @return array An array with the last status ID.
	 */
	public function GetLastStatus()
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

	/*
	 * Queries the DB directly to get the last comment added.
	 *
	 * Basically the same as the method above, query the DB to get the last comment added, ID only. Mostly used for the server response in class Breeze_Ajax.
	 * @access public
	 * @return array An array with the last status ID.
	 */
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

	/*
	 * Decorates the way we call a query. Oh! and calls the right table.
	 *
	 * This is one of the main queries. load all the status from all users.
	 * @access private
	 * @return array an array with the right query call.
	 */
	private function Query($var)
	{
		return $this->query[$var];
	}

	/*
	 * The main method to load all the status
	 *
	 * This is one of the main queries. load all the status from all users.
	 * @access protected
	 * @global $smcFunc the "handling DB stuff" var of SMF
	 * @return array a very big associative array with the status ID as key
	 */
	protected function Status()
	{
		Breeze::Load(array(
			'Subs',
			'UserInfo',
			'Parser',
		));

		$tools = new Breeze_Subs();
		$parser = new Breeze_Parser();
		$this->Status = array();

		/* Use the cache please... */
		if (($this->GetStatus = cache_get_data('Breeze:GetStatus', 120)) == null)
		{
			/* Load all the status, set a limit if things get complicated */
			$result = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}breeze_status
				ORDER BY status_time DESC
				',
				array()
			);

			/* Populate the array like a boss! */
			while ($row = $smcFunc['db_fetch_assoc']($result))
			{
				$this->Status[$row['status_id']] = array(
					'id' => $row['status_id'],
					'owner_id' => $row['status_owner_id'],
					'poster_id' => $row['status_poster_id'],
					'time' => $tools->TimeElapsed($row['status_time']),
					'body' => $parser->Display($row['status_body'])
				);
			}

			/* Cache this beauty */
			cache_put_data('Breeze:GetStatus', $this->Status, 120);
		}

		return $this->Status;
	}

	public function GetStatus()
	{
		return $this->Status;
	}

	/*
	 * Get all status made in X profile page
	 *
	 * Uses the generic class GetReturn.
	 * @see GetReturn()
	 * @access protected
	 * @global $smcFunc the "handling DB stuff" var of SMF
	 * @return array a very big associative array with the status ID as key
	 */
	public function GetStatusByProfile($id)
	{
		return $this->GetReturn('owner_id', $id);
	}

	/* Methods for comments */

	/*
	 * The main method to load all the comments
	 *
	 * This is one of the main queries, load all the commments form all users.
	 * @access protected
	 * @global $smcFunc the "handling DB stuff" var of SMF
	 * @return array a very big associative array with the comment ID as key
	 */
	protected function Comments()
	{
		Breeze::Load(array(
			'Subs',
			'UserInfo',
			'Parser',
		));

		$tools = new Breeze_Subs();
		$parser = new Breeze_Parser();
		$this->Comments = array();

		/* Use the cache please... */
		if (($this->GetStatus = cache_get_data('Breeze:GetComments', 120)) == null)
		{
			/* Load all the comments, set a limit if things get complicated */
			$result = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}breeze_comments
				ORDER BY comments_time DESC
				',
				array()
			);

			/* Populate the array like a comments boss! */
			while ($row = $smcFunc['db_fetch_assoc']($result))
			{
				$this->Comments[$row['comments_id']] = array(
					'id' => $row['comments_id'],
					'status_id' => $row['comments_status_id'],
					'status_owner_id' => $row['comments_status_owner_id'],
					'poster_id' => $row['comments_poster_id'],
					'profile_owner_id' => $row['comments_profile_owner_id'],
					'time' => $tools->TimeElapsed($row['comments_time']),
					'body' => $parser->Display($row['comments_body'])
				);
			}

			/* Cache this beauty */
			cache_put_data('Breeze:GetComments', $this->Comments, 120);
		}

		return $this->Comments;
	}

	public function GetComments()
	{
		return $this->Comments;
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