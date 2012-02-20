<?php

/**
 * Breeze_Query
 *
 * The purpose of this file is to have all queries made by this mod in a single place, probably the most important file and the biggest one too.
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2012, Jessica González
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
 * Portions created by the Initial Developer are Copyright (c) 2012
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
	protected $Settings = array();
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
			'settings' => new Breeze_DB('breeze_user_settings'),
			'modules' => new Breeze_DB('breeze_user_settings_modules'),
			'visitlogs' => new Breeze_DB('breeze_visit_log'),
			'likes' => new Breeze_DB('breeze_likes'),
			'member' => new Breeze_DB('members')
		);
	}

	/* Yes, I used a singleton, so what! */
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
	 * Set the query arrays back to empty
	 *
	 * @access private
	 * @return void
	 */
	private function ResetQueryArrays()
	{
		$this->query_params = array();
		$this->query_data = array();
	}

	/*
	 * Return an associative array based on the entered params
	 *
	 * @access private
	 * @param string $table The name of the table to fetch
	 * @param string $row The name of the row to fetch
	 * @param int $value The value to compare to
	 * @param bool $single true if the query will return only 1 array.
	 * @return array an associative array
	 */
	private function GetReturn($type, $row, $value, $single = false)
	{
		/* Cleaning */
		$this->ResetTemp();
		$this->ResetReturn();

		/* Get the data */
		$this->SwitchData($type);

		/* Needs to be empty by default */
		$this->ResetReturn();

		/* Do this only if there is something to work with */
		if ($this->temp)
		{
			/* Generate an array with a defined key */
			foreach($this->temp as $t)
			{
				if ($t[$row] == $value && !$single)
					$this->r[] = $t;

				/* Get a single value */
				else if ($t[$row] == $value && $single)
					$this->r = $t;
			}
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
			case 'settings':
				$this->temp = $this->Settings();
				break;
			case 'modules':
				$this->temp = $this->Modules();
				break;
			case 'visitlogs':
				$this->temp = $this->VisitLog();
				break;
		}
	}

	/*
	 * Get a single value from an specified array.
	 *
	 * Needs a type, a row and a value, this iterates X array looking for X value in X row. Yes, this can be used to fetch more than I value if you really want to fetch more than 1 value.
	 * @param string $type the data type
	 * @param string $row the row where thoe fetch the value from, should be the actual row name in the array, not the rown name in the DB.
	 * @param mixed $value  Most of the cases will be a int. the int is actually the ID of the particular value you are trying to fetch.
	 * @access private
	 * @return array an array with the requested data
	 */
	public function GetSingleValue($type, $row, $value)
	{
		/* Cleaning */
		$this->ResetTemp();
		$this->ResetReturn();

		return $this->GetReturn($type, $row, $value);
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

		/* Clean the arrays used here, we may need them for something else */
		$this->ResetQueryArrays();

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
	public function GetLastComment()
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
	 * @global array $smcFunc the "handling DB stuff" var of SMF
	 * @return array a very big associative array with the status ID as key
	 */
	protected function Status()
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
		if (($this->Status = cache_get_data('Breeze:Status', 120)) == null)
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
			cache_put_data('Breeze:Status', $this->Status, 120);
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
	 * @param int $id the ID of the user that owns the profile page, it does not matter who made that status as long as the status was made in X profile page.
	 * @access public
	 * @return array an array containing all the status made in X profile page
	 */
	public function GetStatusByProfile($id)
	{
		return $this->GetReturn('status', 'owner_id', $id);
	}

	/*
	 * Get a single status based on the ID
	 *
	 * This should return just one value, if it returns more, then we have a bug somewhere or you didn't provide a valid ID
	 * @see GetReturn()
	 * @param int $id the ID of status you want to fetch.
	 * @access public
	 * @return array an array containing all the status made in X profile page
	 */
	public function GetStatusByID($id)
	{
		return $this->GetReturn('status', 'status_id', $id);
	}

	/*
	 * Get all status made by X user.
	 *
	 * This returns all the status made by x user, it does not matter on what profile page they were made.
	 * @see GetReturn()
	 * @param int $id the ID of the user that you want to fetch the status from.
	 * @access public
	 * @return array an array containing all the status made in X profile page
	 */
	public function GetStatusByUser($id)
	{
		return $this->GetReturn('status', 'status_id', $id);
	}


	/* Methods for comments */

	/*
	 * The main method to load all the comments
	 *
	 * This is one of the main queries, load all the commments form all users.
	 * @access protected
	 * @global array $smcFunc the "handling DB stuff" var of SMF
	 * @return array a very big associative array with the comment ID as key
	 */
	protected function Comments()
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
		if (($this->Comments = cache_get_data('Breeze:Comments', 120)) == null)
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
			cache_put_data('Breeze:Comments', $this->Comments, 120);
		}

		return $this->Comments;
	}

	/*
	 * Returns all comments in the comments array
	 *
	 * @access public
	 * @return array an array containing all comments. ID as the key.
	 */
	public function GetComments()
	{
		return $this->Comments;
	}

	/*
	 * Get all comments made in X profile page
	 *
	 * Uses the generic class GetReturn.
	 * @see GetReturn()
	 * @access public
	 * @return array an array containing all comments made in X profile page
	 */
	public function GetCommentsByProfile($id)
	{
		return $this->GetReturn('comments', 'profile_owner_id', $id);
	}

	public function GetCommentsByStatus($id)
	{
		return $this->GetReturn('comments', 'status_id', $id);
	}

	/* Settings */

	/*
	 * The main method to load all the settings from all users
	 *
	 * This is one of the main queries. load all the settings from all users. We set the cache here on 4 minutes since the settings aren't updated that often.
	 * @access protected
	 * @global array $smcFunc the "handling DB stuff" var of SMF
	 * @return array a very big associative array with the user ID as key
	 */
	protected function Settings()
	{
		global $smcFunc;

		/* Use the cache please... */
		if (($this->Settings = cache_get_data('Breeze:Settings', 240)) == null)
		{
			/* Load all the status, set a limit if things get complicated */
			$result = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}breeze_user_settings
				',
				array()
			);

			/* Populate the array like a boss! */
			while ($row = $smcFunc['db_fetch_assoc']($result))
			{
				$this->Settings[$row['user_id']] = array(
					'user_id' => $row['user_id'],
					'enable_wall' => $row['enable_wall'],
					'kick_ignored' => $row['kick_ignored'],
					'enable_visits_module' => $row['enable_visits_module'],
					'visits_module_timeframe' => $row['visits_module_timeframe'],
					'pagination_number' => $row['pagination_number']
				);
			}

			/* Cache this beauty */
			cache_put_data('Breeze:Settings', $this->Settings, 240);
		}

		return $this->Settings;
	}

	/*
	 * Gets all the settings from one user
	 *
	 * Gets all the users preferences
	 * @access public
	 * @param int $user  the User ID
	 * @return array an array with the users settings
	 */
	public function GetSettingsByUser($user)
	{
		return $this->GetReturn('settings', 'user_id', $user, true);
	}

	/* Editing methods */

	public function InsertStatus($array)
	{
		/* We dont need this anymore */
		$this->KillCache('Status');

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
		$this->KillCache('Comments');

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
		$this->KillCache('Status');

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

	public function GetUserSettings($user)
	{
		return $this->GetReturn('settings', 'user_id', $user, true);
	}

	public function UpdateUserSettings($data)
	{
		$params = array(
			'set' =>
				'enable_wall = {int:enable_wall},
				kick_ignored = {int:kick_ignored},
				visits_module_timeframe = {int:visits_module_timeframe},
				enable_visits_module = {int:enable_visits_module},
				pagination_number = {int:pagination_number}',
			'where' =>'user_id = {int:user_id}',
		);

		/* Do the update */
		$this->Query('settings')->Params($params, $data);
		$this->Query('settings')->UpdateData();
	}

	public function InsertUserSettings($data)
	{
		$type = array(
			'user_id' => 'int',
			'enable_wall' => 'int',
			'kick_ignored' =>'int',
			'pagination_number' => 'int',
			'enable_visits_module' => 'int',
			'visits_module_timeframe' => 'int'
		);

		$indexes = array(
			'user_id'
		);

		/* Insert */
		$this->Query('settings')->InsertData($type, $data, $indexes);
	}

	public function GetUserIgnoreList($id)
	{
		$this->query_params = array(
			'rows' =>'pm_ignore_list',
			'where' => 'id_member = {int:id}',
		);

		$this->query_data = array(
			'id' => $id
		);

		$this->Query('member')->Params($this->query_params, $this->query_data);
		$this->Query('member')->GetData(null, true);
		$temp = $this->Query('member')->DataResult();

		/* Done? set the arrays back to empty */
		$this->ResetQueryArrays();

		if (!empty($temp['pm_ignore_list']))
			return explode(',', $temp['pm_ignore_list']);

		else
			return array();
	}

	/*
	 * The main method to load all the settings from all users
	 *
	 * This is one of the main queries. load all the settings from all users. We set the cache here on 4 minutes since the settings aren't updated that often.
	 * @access protected
	 * @global array $smcFunc the "handling DB stuff" var of SMF
	 * @return array a very big associative array with the user ID as key
	 */
	protected function VisitLog()
	{
		global $smcFunc;

		/* Use the cache please... */
		if (($this->VisitLog = cache_get_data('Breeze:VisitLog', 120)) == null)
		{
			/* Load all the status, set a limit if things get complicated */
			$result = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}breeze_visit_log
				',
				array()
			);

			/* Populate the array like a boss! */
			while ($row = $smcFunc['db_fetch_assoc']($result))
			{
				$this->VisitLog[$row['id']] = array(
					'id' => $row['id'],
					'profile' => $row['profile'],
					'user' => $row['user'],
					'time' => timeformat($row['time'])
				);
			}

			/* Cache this beauty */
			cache_put_data('Breeze:VisitLog', $this->Settings, 240);
		}

		return $this->VisitLog;
	}

	/*
	 * Return a boolean, true if the user already visited the profile, false otherwise
	 *
	 * Get all the visits to X profile, compare if the visitor has already visited that profile, return a boolean.
	 * @access protected
	 * @param int $profile the User's ID that owns the profile
	 * @param int $visitor The User's ID who is visiting this profile
	 * @return bool
	 */
	protected function GetUniqueVisit($profile, $visitor)
	{
		$temp = $this->GetReturn('visitlogs', 'profile', $profile, false);
		$temp2 = array();

		if (!empty($temp))
		{
			foreach($temp as $t)
				$temp2[] = $t['user'];

			if (in_array($visitor, $temp2))
				return true;

			else
				return false;
		}

		else
			return false;
	}

	/*
	 * Logs profile visitors
	 *
	 * Checks if the visitor has already been here, if true, just update the time, otherwise create the entry on the DB, generates a new cache entry.
	 * @access public
	 * @param int $profile the User's ID that owns the profile
	 * @param int $visitor The User's ID who is visiting this profile
	 * @return void
	 */
	public function WriteProfileVisit($profile, $visitor)
	{
		global $context;

		if (empty($profile) || empty($visitor))
			return;

		/* Don't log this if the user is visiting his/her own profile */
		if ($profile == $visitor)
			return;

		/* Do not log guest people */
		if ($context['user']['is_guest'])
			return;

		/* Get all visits to this profile */
		$already = $this->GetUniqueVisit($profile, $visitor);

		/* Is this your first time? */
		if ($already == false)
		{
			$insert_data = array(
				'profile' => 'int',
				'user' => 'int',
				'time' => 'int'
			);
			$insert_values = array(
				$profile,
				$visitor,
				time()
			);
			$insert_indexes = array(
				'id'
			);

			$this->Query('visitlogs')->InsertData($insert_data, $insert_values, $insert_indexes);
		}

		/* No? then update the time*/
		else
		{
			$this->query_params = array(
				'set' =>'time = {int:time}',
				'where' => 'profile = {int:profile} AND user = {int:user}',
			);

			$this->query_data = array(
				'user' => $visitor,
				'profile' => $profile,
				'time' => time()
			);

			$this->Query('visitlogs')->Params($this->query_params, $this->query_data);
			$this->Query('visitlogs')->UpdateData();
		}

		/* Clean the arrays */
		$this->ResetQueryArrays();

		/* Set new cache */
		$this->KillCache('VisitLog');
	}

	/*
	 * Get's all the visits to X profile in X period of time
	 *
	 * The time period is defined by the user in their wall settings.
	 * @access public
	 * @param int $profile the User's ID that owns the profile
	 * @param int $time a simple number that represents the timeframe
	 * @return array
	 */
	public function GetProfilevisits($profile, $time)
	{
		/* Get the user's choice */
		$date = $this->GetUserSettings($profile);

		/* Set the time frame */
		switch($date)
		{
			case 1:
				$timeframe = strtotime('-1 hour');
				break;
			case 2:
				$timeframe = strtotime('-1 day');
				break;
			case 3:
				$timeframe = strtotime('-1 week');
				break;
			case 4:
				$timeframe = strtotime('-1 month');
				break;
			default:
				$timeframe = strtotime('-1 week');
		}

		$params = array(
			'rows' => '*',
			'where' => 'time >= {int:timeframe} AND profile = {int:profile}'
		);
		$data = array(
			'timeframe' => $timeframe,
			'profile' => $profile
		);

		$this->Query('visitlogs')->Params($params, $data);
		$this->Query('visitlogs')->GetData('id');
		$temp = $this->Query('visitlogs')->DataResult();

		if (!empty($temp))
			return $temp;

		else
			return array();
	}
}