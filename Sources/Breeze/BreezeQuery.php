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
	protected $_members = array();
	protected $_temp;
	private $data = array();
	private $query_params = array('rows' =>'*');
	private $query_data = array();

	protected function __construct()
	{
		$this->_tables = array(
			'status' => array(
				'name' => 'status',
				'table' => 'breeze_status',
				'property' => '_status',
			),
			'comments' => array(
				'name' => 'comments',
				'table' => 'breeze_comments',
				'property' => '_comments',
			),
			'members' => array(
				'name' => 'member',
				'table' => 'members',
				'property' => '_members',
			),
		);
	}

	/* Yes, I used a singleton, so what! */
	public static function getInstance()
	{
		if (!self::$_instance)
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/*
	 * Decorates the way we call a query. Oh! and calls the right table.
	 *
	 * Call the right table object.
	 * @access protected
	 * @return object a new DB object.
	 */
	protected function query($var)
	{
		if (array_key_exists($var, $this->_tables))
			return new BreezeDB($this->_tables[$var]['table']);

		else
			return false;
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
	 * @access protected
	 * @return void
	 */
	protected function resetTemp()
	{
		$this->_temp = array();
	}

	protected function resetQueryArrays()
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
		/* Get the data */
		$this->switchData($type);

		$return ='';

		/* Do this only if there is something to work with */
		if ($this->_temp)
		{
			/* Generate an array with a defined key */
			foreach($this->_temp as $t)
			{
				if ($t[$row] == $value && !$single)
					$return[] = $t;

				/* Get a single value */
				else if ($t[$row] == $value && $single)
					$return = $t;
			}
		}

		/* Cleaning */
		$this->resetTemp();

		/* Return the info we want as we want it */
		return $return;
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
		$property = $this->_tables[$type]['property'];
		$method = $this->_tables[$type]['name'];

		if (array_key_exists($type, $this->_tables))
			$this->_temp = $this->$property ? $this->$property : $this->$method();
	}

	/*
	 * Get a single value from an specified array.
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
		/* Get the value directly from the DB */
		$this->_queryParams = array(
			'rows' => 'status_id',
			'order' => '{raw:sort}',
			'limit' => '{int:limit}'
		);

		$this->_queryData = array(
			'sort' => 'status_id DESC',
			'limit' => 1
		);
		$this->query($this->_tables['status']['name'])->params($this->_queryParams, $this->_queryData);
		$this->query($this->_tables['status']['name'])->getData(null, true);

		/* Clean the arrays used here, we may need them for something else */
		$this->resetQueryArrays();

		/* Done? */
		return $this->query($this->_tables['status']['name'])->dataResult();
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
		/* Get the value directly from the DB */
		$this->_queryParams = array(
			'rows' => 'comments_id',
			'order' => '{raw:sort}',
			'limit' => '{int:limit}'
		);

		$this->_queryData = array(
			'sort' => 'comments_id DESC',
			'limit' => 1
		);
		$this->query($this->_tables['comments']['name'])->params($this->_queryParams, $this->_queryData);
		$this->query($this->_tables['comments']['name'])->getData(null, true);

		/* Done? */
		return $this->query($this->_tables['comments']['name'])->dataResult();
	}

	/*
	 * The main method to load all the status
	 *
	 * This is one of the main queries. load all the status from all users.
	 * @access protected
	 * @global array $smcFunc the "handling DB stuff" var of SMF
	 * @return array a very big associative array with the status ID as key
	 */
	protected function status()
	{
		global $smcFunc;

		$tools = Breeze::tools();
		$gSettings = Breeze::settings();

		/* Use the cache please... */
		if (($this->_status = cache_get_data('Breeze:'. $this->_tables['status']['name'], 120)) == null)
		{
			/* Load all the status, set a limit if things get complicated */
			$result = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}breeze_status
				'. ($gSettings->enable('admin_enable_limit') && $gSettings->enable('admin_limit_timeframe') ? 'WHERE status_time >= {int:status_time}' : '' ).'
				ORDER BY status_time DESC
				',
				array(
					'status_time' => $gSettings->getSetting('admin_limit_timeframe'),
				)
			);

			/* Populate the array like a boss! */
			while ($row = $smcFunc['db_fetch_assoc']($result))
			{
				$this->_status[$row['status_id']] = array(
					'id' => $row['status_id'],
					'owner_id' => $row['status_owner_id'],
					'poster_id' => $row['status_poster_id'],
					'time' => $tools->timeElapsed($row['status_time']),
					'body' => $row['status_body']
				);
			}

			/* Cache this beauty */
			cache_put_data('Breeze:'. $this->_tables['status']['name'], $this->_status, 120);
		}

		return $this->_status;
	}

	public function getStatus()
	{
		return $this->_status ? $this->_status : $this->status();
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
	public function getStatusByProfile($id)
	{
		return $this->getReturn($this->_tables['status']['name'], 'owner_id', $id);
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
	public function getStatusByID($id)
	{
		return $this->getReturn('status', 'id', $id, true);
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
	public function getStatusByUser($id)
	{
		return $this->getReturn('status', 'status_user', $id);
	}

	/*
	 * Get the latest Status in the Status array.
	 *
	 * This returns the last status added to the array.
	 * @access public
	 * @return array the last status added to the Status array
	 */
	public function getStatusByLast()
	{
		$array = $this->_status ? $this->_status : $this->status();

		return array_shift(array_values($array));
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
	protected function comments()
	{
		global $smcFunc;

		$tools = Breeze::tools();
		$gSettings = Breeze::settings();

		/* Use the cache please... */
		if (($this->comments = cache_get_data('Breeze:'. $this->_tables['comments']['name'], 120)) == null)
		{
			/* Load all the comments, set a limit if things get complicated */
			$result = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}breeze_comments
				'. ($gSettings->enable('admin_enable_limit') && $gSettings->enable('admin_limit_timeframe') ? 'WHERE comments_time >= {int:comments_time}' : '' ).'
				ORDER BY comments_time ASC
				',
				array(
					'comments_time' => $gSettings->getSetting('admin_limit_timeframe'),
				)
			);

			/* Populate the array like a comments boss! */
			while ($row = $smcFunc['db_fetch_assoc']($result))
			{
				$this->_comments[$row['comments_id']] = array(
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
			cache_put_data('Breeze:'. $this->_tables['comments']['name'], $this->_comments, 120);
		}

		return $this->_comments;
	}

	/*
	 * Returns all comments in the comments array
	 *
	 * @access public
	 * @return array an array containing all comments. ID as the key.
	 */
	public function getComments()
	{
		return $this->_comments ? $this->_comments : $this->comments();
	}

	/*
	 * Get all comments made in X profile page
	 *
	 * Uses the generic class GetReturn.
	 * @see GetReturn()
	 * @access public
	 * @return array an array containing all comments made in X profile page
	 */
	public function getCommentsByProfile($id)
	{
		return $this->getReturn($this->_tables['comments']['name'], 'profile_owner_id', $id);
	}

	public function getCommentsByStatus($id)
	{
		/* Do not call the Comments method for every status, use it only once */
		$this->resetTemp();
		$temp2 = array();

		$this->_temp = $this->_comments ? $this->_comments : $this->comments();

		foreach($this->_temp as $c)
			if ($c['status_id'] == $id)
				$temp2[$c['id']] = $c;

		return $temp2;
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

		$this->query($this->_tables['status']['name'])->insertData($data, $array, $indexes);
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

		$this->query($this->_tables['comments']['name'])->insertData($data, $array, $indexes);
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
		$this->query($this->_tables['comments']['name'])->params($paramsc, $data);
		$this->query($this->_tables['comments']['name'])->deleteData();

		$this->query($this->_tables['status']['name'])->params($params, $data);
		$this->query($this->_tables['status']['name'])->deleteData();
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

		$this->query($this->_tables['comments']['name'])->params($params, $data);
		$this->query($this->_tables['comments']['name'])->deleteData();
	}

	protected function members()
	{
		global $smcFunc;

		/* Use the cache please... */
		if (($this->_members = cache_get_data('Breeze:members', 120)) == null)
		{
			/* Load all the settings from all users */
			$result = $smcFunc['db_query']('', '
				SELECT wall_settings, pm_ignore_list, id_member, enable_wall
				FROM {db_prefix}'. $this->_tables['members']['table'] .'
				',
				array()
			);

			/* Populate the array like a boss! */
			while ($row = $smcFunc['db_fetch_assoc']($result))
			{
				$this->_members[$row['id_member']] = $row;
			}

			/* Cache this beauty */
			cache_put_data('Breeze:members', $this->_members, 120);
		}

		return $this->_members;
	}

	public function getUserSettings($user)
	{
		$return = $this->_members ? $this->_members : $this->members();

		if (!empty($return[$user]))
			return $return[$user];

		else
			return false;
	}

	public function insertUserSettings($user, $values)
	{
		$data = array(
			'enable_wall' => 'int',
			'wall_settings' => 'string',
		);

		$indexes = array(
			'id_member'
		);

		$this->query($this->_tables['members']['name'])->insertData($data, $values, $indexes);
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

		/* Get all visits to this profile */
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
	 * Get's all the visits to X profile in X period of time
	 *
	 * The time period is defined by the user in their wall settings.
	 * @access public
	 * @param int $profile the User's ID that owns the profile
	 * @param int $time a simple number that represents the timeframe
	 * @return array
	 */
	public function getProfilevisits($profile, $time)
	{
		/* Get the user's choice */
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
	protected function Notifications()
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

	public function getNotifications()
	{
		return $this->Notifications ? $this->Notifications : $this->Notifications();
	}

	public function InsertNotification($array)
	{
		/* We dont need this anymore */
		$this->killCache('Notifications');

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

	public function MarkAsReadNotification($id)
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

	public function DeleteNotification($id)
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