<?php

/**
 * BreezeQuery
 *
 * The purpose of this file is to have all queries made by this mod in a single place, probably the most important file and the biggest one too.
 * @package Breeze mod
 * @version 1.0 Beta 2
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

class BreezeQuery
{
	private static $_instance;
	protected $_status = array();
	protected $_comments = array();
	protected $_settings = array();
	protected $_likes = array();
	protected $_notifications = array();
	private $_query = array();
	private $_data = array();
	private $_queryParams = array('rows' =>'*');
	private $_queryData = array();
	private $_temp = array();
	private $_temp2 = array();
	private $_valid = false;
	private $_globalSettings;

	protected function __construct()
	{
		Breeze::loadFile(array(
			'DB',
			'Settings'
		));

		$this->_query = array(
			'status' => new BreezeDB('breeze_status'),
			'comments' => new BreezeDB('breeze_comments'),
			'settings' => new BreezeDB('breeze_user_settings'),
			'modules' => new BreezeDB('breeze_user_settings_modules'),
			'visitlogs' => new BreezeDB('breeze_visit_log'),
			'likes' => new BreezeDB('breeze_likes'),
			'member' => new BreezeDB('members'),
			'notifications' => new BreezeDB('breeze_notifications')
		);

		$this->_globalSettings = BreezeSettings::getInstance();
	}

	/* Yes, I used a singleton, so what! */
	public static function getInstance()
	{
		if (!self::$_instance)
		{
			self::$_instance = new BreezeQuery();
		}

		return self::$_instance;
	}

	/*
	 * Cleans the old cache value
	 *
	 * Disclaimer: Killing in breeze world means replace the existing cache data with a null value so SMF generates a new cache...
	 * @access public
	 * @param mixed $type the name of value(s) to be deleted
	 * @return void
	 */
	public function killCache($type)
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
	private function resetTemp()
	{
		$this->_temp = array();
	}

	/*
	 * Set the return array back to null
	 *
	 * @access private
	 * @return void
	 */
	private function resetReturn()
	{
		$this->r = array();
	}

	/*
	 * Set the query arrays back to empty
	 *
	 * @access private
	 * @return void
	 */
	private function resetQueryArrays()
	{
		$this->_queryParams = array();
		$this->_queryData = array();
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
	private function getReturn($type, $row, $value, $single = false)
	{
		/* Cleaning */
		$this->resetTemp();
		$this->resetReturn();

		/* get the data */
		$this->switchData($type);

		/* Do this only if there is something to work with */
		if ($this->_temp)
		{
			/* Generate an array with a defined key */
			foreach($this->_temp as $t)
			{
				if ($t[$row] == $value && !$single)
					$this->r[] = $t;

				/* get a single value */
				else if ($t[$row] == $value && $single)
					$this->r = $t;
			}
		}

		/* Clean the Temp array */
		$this->resetTemp();

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
	private function switchData($type)
	{
		switch ($type)
		{
			case 'status':
				$this->_temp = $this->status();
				break;
			case 'comments':
				$this->_temp = $this->comments();
				break;
			case 'likes':
				$this->_temp = $this->likes();
				break;
			case 'settings':
				$this->_temp = $this->settings();
				break;
			case 'modules':
				$this->_temp = $this->modules();
				break;
			case 'visitlogs':
				$this->_temp = $this->visitLog();
				break;
			case 'notifications':
				$this->_temp = $this->notifications();
				break;
		}
	}

	/*
	 * get a single value from an specified array.
	 *
	 * Needs a type, a row and a value, this iterates X array looking for X value in X row. Yes, this can be used to fetch more than one value if you really want to fetch more than 1 value.
	 * @param string $type the data type
	 * @param string $row the row where thoe fetch the value from, should be the actual row name in the array, not the rown name in the DB.
	 * @param mixed $value  Most of the cases will be a int. the int is actually the ID of the particular value you are trying to fetch.
	 * @access private
	 * @return array an array with the requested data
	 */
	public function getSingleValue($type, $row, $value)
	{
		/* Cleaning */
		$this->resetTemp();
		$this->resetReturn();

		return $this->getReturn($type, $row, $value);
	}

	/*
	 * Queries the DB directly to get the last status added.
	 *
	 * It is not reliable to use the cache array for this one so let's do a query here. We will only fetch the ID because that is the only thing we want. Mostly used for the server response in class BreezeAjax.
	 * @access public
	 * @return array An array with the last status ID.
	 */
	public function getLastStatus()
	{
		/* get the value directly from the DB */
		$this->_queryParams = array(
			'rows' => 'status_id',
			'order' => '{raw:sort}',
			'limit' => '{int:limit}'
		);

		$this->_queryData = array(
			'sort' => 'status_id DESC',
			'limit' => 1
		);
		$this->query('status')->params($this->_queryParams, $this->_queryData);
		$this->query('status')->getData(null, true);

		/* Clean the arrays used here, we may need them for something else */
		$this->resetQueryArrays();

		/* Done? */
		return $this->query('status')->dataResult();
	}

	/*
	 * Queries the DB directly to get the last comment added.
	 *
	 * Basically the same as the method above, query the DB to get the last comment added, ID only. Mostly used for the server response in class BreezeAjax.
	 * @access public
	 * @return array An array with the last status ID.
	 */
	public function getLastComment()
	{
		/* get the value directly from the DB */
		$this->_queryParams = array(
			'rows' => 'comments_id',
			'order' => '{raw:sort}',
			'limit' => '{int:limit}'
		);

		$this->_queryData = array(
			'sort' => 'comments_id DESC',
			'limit' => 1
		);
		$this->query('comments')->params($this->_queryParams, $this->_queryData);
		$this->query('comments')->getData(null, true);

		/* Done? */
		return $this->query('comments')->dataResult();
	}

	/*
	 * Decorates the way we call a query. Oh! and calls the right table.
	 *
	 * Call the right value in the query object.
	 * @access private
	 * @return array an array with the right query call.
	 */
	private function Query($var)
	{
		return $this->_query[$var];
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

		Breeze::loadFile(array(
			'Subs'
		));

		$tools = new BreezeSubs();

		/* Use the cache please... */
		if (($this->Status = cache_get_data('Breeze:Status', 120)) == null)
		{
			/* Load all the status, set a limit if things get complicated */
			$result = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}breeze_status
				'. ($this->_globalSettings->Enable('admin_enable_limit') && $this->_globalSettings->Enable('admin_limit_timeframe') ? 'WHERE status_time >= {int:status_time}' : '' ).'
				ORDER BY status_time DESC
				',
				array(
					'status_time' => $this->_globalSettings->getSetting('admin_limit_timeframe'),
				)
			);

			/* Populate the array like a boss! */
			while ($row = $smcFunc['db_fetch_assoc']($result))
			{
				$this->Status[$row['status_id']] = array(
					'id' => $row['status_id'],
					'owner_id' => $row['status_owner_id'],
					'poster_id' => $row['status_poster_id'],
					'time' => $tools->timeElapsed($row['status_time']),
					'body' => $row['status_body']
				);
			}

			/* Cache this beauty */
			cache_put_data('Breeze:Status', $this->Status, 120);
		}

		return $this->Status;
	}

	public function getStatus()
	{
		return $this->Status ? $this->Status : $this->status();
	}

	/*
	 * get all status made in X profile page
	 *
	 * Uses the generic class getReturn.
	 * @see getReturn()
	 * @param int $id the ID of the user that owns the profile page, it does not matter who made that status as long as the status was made in X profile page.
	 * @access public
	 * @return array an array containing all the status made in X profile page
	 */
	public function getStatusByprofile($id)
	{
		return $this->getReturn('status', 'owner_id', $id);
	}

	/*
	 * get a single status based on the ID
	 *
	 * This should return just one value, if it returns more, then we have a bug somewhere or you didn't provide a valid ID
	 * @see getReturn()
	 * @param int $id the ID of status you want to fetch.
	 * @access public
	 * @return array an array containing all the status made in X profile page
	 */
	public function getStatusByID($id)
	{
		return $this->getReturn('status', 'status_id', $id);
	}

	/*
	 * get all status made by X user.
	 *
	 * This returns all the status made by x user, it does not matter on what profile page they were made.
	 * @see getReturn()
	 * @param int $id the ID of the user that you want to fetch the status from.
	 * @access public
	 * @return array an array containing all the status made in X profile page
	 */
	public function getStatusByUser($id)
	{
		return $this->getReturn('status', 'status_user', $id);
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

		Breeze::loadFile(array(
			'Subs'
		));

		$tools = new BreezeSubs();

		/* Use the cache please... */
		if (($this->Comments = cache_get_data('Breeze:Comments', 120)) == null)
		{
			/* Load all the comments, set a limit if things get complicated */
			$result = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}breeze_comments
				'. ($this->_globalSettings->Enable('admin_enable_limit') && $this->_globalSettings->Enable('admin_limit_timeframe') ? 'WHERE comments_time >= {int:comments_time}' : '' ).'
				ORDER BY comments_time ASC
				',
				array(
					'comments_time' => $this->_globalSettings->getSetting('admin_limit_timeframe'),
				)
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
					'time' => $tools->timeElapsed($row['comments_time']),
					'body' => $row['comments_body']
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
	public function getComments()
	{
		return $this->Comments ? $this->Comments : $this->Comments();
	}

	/*
	 * get all comments made in X profile page
	 *
	 * Uses the generic class getReturn.
	 * @see getReturn()
	 * @access public
	 * @return array an array containing all comments made in X profile page
	 */
	public function getCommentsByprofile($id)
	{
		return $this->getReturn('comments', 'profile_owner_id', $id);
	}

	public function getCommentsByStatus($id)
	{
		/* Do not call the Comments method for every status, use it only once */
		$this->resetTemp();
		$temp2 = array();

		$this->_temp = $this->Comments ? $this->Comments : $this->Comments();

		foreach($this->_temp as $c)
			if ($c['status_id'] == $id)
				$temp2[$c['id']] = $c;

		return $temp2;
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
		if (($this->_settings = cache_get_data('Breeze:Settings', 240)) == null)
		{
			/* Load all the settings from all users */
			$result = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}breeze_user_settings
				',
				array()
			);

			/* Populate the array like a boss! */
			while ($row = $smcFunc['db_fetch_assoc']($result))
			{
				$this->_settings[$row['user_id']] = array(
					'user_id' => $row['user_id'],
					'enable_wall' => $row['enable_wall'],
					'kick_ignored' => $row['kick_ignored'],
					'enableVisitsModule' => $row['enableVisitsModule'],
					'visits_module_timeframe' => $row['visits_module_timeframe'],
					'pagination_number' => $row['pagination_number']
				);
			}

			/* Cache this beauty */
			cache_put_data('Breeze:Settings', $this->_settings, 240);
		}

		return $this->_settings;
	}

	/*
	 * gets all the settings from one user
	 *
	 * gets all the users preferences
	 * @access public
	 * @param int $user  the User ID
	 * @return array an array with the users settings
	 */
	public function getSettingsByUser($user)
	{
		return $this->getReturn('settings', 'user_id', $user, true);
	}

	/* Editing methods */

	public function insertStatus($array)
	{
		/* We dont need this anymore */
		$this->killCache('Status');

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

		$this->query('status')->insertData($data, $array, $indexes);
	}

	public function insertComment($array)
	{
		/* We dont need this anymore */
		$this->killCache('Comments');

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

		$this->query('comments')->insertData($data, $array, $indexes);
	}

	public function deleteStatus($id)
	{
		/* We dont need this anymore */
		$this->killCache('Status');

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
		$this->query('comments')->params($paramsc, $data);
		$this->query('comments')->deleteData();

		$this->query('status')->params($params, $data);
		$this->query('status')->deleteData();
	}

	public function deleteComment($id)
	{
		/* Delete! */
		$params = array(
			'where' => 'comments_id = {int:id}'
		);

		$data = array(
			'id' => $id
		);

		$this->query('comments')->params($params, $data);
		$this->query('comments')->deleteData();
	}

	public function getUserSettings($user)
	{
		return $this->getReturn('settings', 'user_id', $user, true);
	}

	public function updateUserSettings($data)
	{
		$params = array(
			'set' =>
				'enable_wall = {int:enable_wall},
				kick_ignored = {int:kick_ignored},
				visits_module_timeframe = {int:visits_module_timeframe},
				enableVisitsModule = {int:enableVisitsModule},
				pagination_number = {int:pagination_number}',
			'where' =>'user_id = {int:user_id}',
		);

		/* Do the update */
		$this->query('settings')->params($params, $data);
		$this->query('settings')->updateData();
	}

	public function insertUserSettings($data)
	{
		$type = array(
			'user_id' => 'int',
			'enable_wall' => 'int',
			'kick_ignored' =>'int',
			'pagination_number' => 'int',
			'enableVisitsModule' => 'int',
			'visits_module_timeframe' => 'int'
		);

		$indexes = array(
			'user_id'
		);

		/* Insert */
		$this->query('settings')->insertData($type, $data, $indexes);
	}

	public function getUserIgnoreList($id)
	{
		$this->_queryParams = array(
			'rows' =>'pm_ignore_list',
			'where' => 'id_member = {int:id}',
		);

		$this->_queryData = array(
			'id' => $id
		);

		$this->query('member')->params($this->_queryParams, $this->_queryData);
		$this->query('member')->getData(null, true);
		$temp = $this->query('member')->dataResult();

		/* Done? set the arrays back to empty */
		$this->resetQueryArrays();

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
			cache_put_data('Breeze:VisitLog', $this->_settings, 240);
		}

		return $this->VisitLog;
	}

	/*
	 * Return a boolean, true if the user already visited the profile, false otherwise
	 *
	 * get all the visits to X profile, compare if the visitor has already visited that profile, return a boolean.
	 * @access protected
	 * @param int $profile the User's ID that owns the profile
	 * @param int $visitor The User's ID who is visiting this profile
	 * @return bool
	 */
	protected function getUniqueVisit($profile, $visitor)
	{
		$temp = $this->getReturn('visitlogs', 'profile', $profile, false);
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

		/* get all visits to this profile */
		$already = $this->getUniqueVisit($profile, $visitor);

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

			$this->query('visitlogs')->insertData($insert_data, $insert_values, $insert_indexes);
		}

		/* No? then update the time*/
		else
		{
			$this->_queryParams = array(
				'set' =>'time = {int:time}',
				'where' => 'profile = {int:profile} AND user = {int:user}',
			);

			$this->_queryData = array(
				'user' => $visitor,
				'profile' => $profile,
				'time' => time()
			);

			$this->query('visitlogs')->params($this->_queryParams, $this->_queryData);
			$this->query('visitlogs')->updateData();
		}

		/* Clean the arrays */
		$this->resetQueryArrays();

		/* Set new cache */
		$this->killCache('VisitLog');
	}

	/*
	 * get's all the visits to X profile in X period of time
	 *
	 * The time period is defined by the user in their wall settings.
	 * @access public
	 * @param int $profile the User's ID that owns the profile
	 * @param int $time a simple number that represents the timeframe
	 * @return array
	 */
	public function getProfilevisits($profile, $time)
	{
		/* get the user's choice */
		$date = $this->getUserSettings($profile);

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

		$this->query('visitlogs')->params($params, $data);
		$this->query('visitlogs')->getData('id');
		$temp = $this->query('visitlogs')->dataResult();

		if (!empty($temp))
			return $temp;

		else
			return array();
	}

	/*
	 * The main method to load all the notifications
	 *
	 * This is one of the main queries. load all the notifications from all users.
	 * @access protected
	 * @global array $smcFunc the "handling DB stuff" var of SMF
	 * @return array a very big associative array with the notification ID as key
	 */
	protected function notifications()
	{
		global $smcFunc;

		/* Use the cache please... */
		if (($this->Notifications = cache_get_data('Breeze:Notifications', 120)) == null)
		{
			$result = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}breeze_notifications
				',
				array()
			);

			/* Populate the array like a boss! */
			while ($row = $smcFunc['db_fetch_assoc']($result))
			{
				$this->Notifications[$row['id']] = array(
					'id' => $row['id'],
					'user' => $row['user'],
					'type' => $row['type'],
					'time' => $row['time'],
					'read' => $row['read'],
					'content' => json_decode($row['content'])
				);
			}

			/* Cache this beauty */
			cache_put_data('Breeze:Notifications', $this->Notifications, 120);
		}

		return $this->Notifications;
	}

	public function getnotifications()
	{
		return $this->Notifications ? $this->Notifications : $this->notifications();
	}
	
	public function InsertNotification($array)
	{
		/* We dont need this anymore */
		$this->killCache('Notifications');

		/* Convert to a json string */
		$array['content'] = json_encode($array['content']);

		/* Insert! */
		$data = array(
			'user' => 'int',
			'type' => 'string',
			'time' => 'int',
			'read' => 'int',
			'content' => 'string',
		);

		$indexes = array(
			'id'
		);

		$this->query('notifications')->insertData($data, $array, $indexes);
	}

	public function markAsReadNotification($id)
	{
		/* We dont need this anymore */
		$this->killCache('Notifications');

		/* Mark as read */
		$params = array(
			'set' => 'read = {int:read}',
			'where' => 'id = {int:id}'
		);

		$data = array(
			'read' => 1,
			'id' => $id
		);

		$this->query('notifications')->params($params, $data);
		$this->query('notifications')->updateData();
	}

	public function deleteNotification($id)
	{
		/* We dont need this anymore */
		$this->killCache('Notifications');

		/* Delete! */
		$params = array(
			'where' => 'id = {int:id}'
		);

		$data = array(
			'id' => $id
		);

		$this->query('notifications')->params($params, $data);
		$this->query('notifications')->deleteData();
	}

	public function getNotificationByUser($user)
	{
		return $this->getReturn('notifications', 'user', $user);
	}

	public function getNotificationByType($type)
	{
		return $this->getReturn('notifications', 'type', $type);
	}
}

/*
 * Saturday is almost over
 * getting high on stormy weather
 * something to remember
 * a sunny day can make you crumble.
 */