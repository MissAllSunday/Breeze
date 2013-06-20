<?php

/**
 * BreezeQuery
 *
 * The purpose of this file is to have all queries made by this mod in a single place, probably the most important file and the biggest one too.
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013 Jessica González
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
	die('No direct access...');

class BreezeQuery extends Breeze
{
	private static $_instance;
	protected $_status = array();
	protected $_noti = array();
	protected $_comments = array();
	protected $_members = array();
	protected $_temp;
	private $data = array();
	private $query_params = array('rows' => '*');
	private $query_data = array();
	private $_smcFunc;
	protected static$usersArray;

	/**
	 * BreezeQuery::__construct()
	 *
	 * Creates a multidimensional array with all the details about the tables used in Breeze
	 * @return
	 */
	public function __construct($settings, $text, $tools, $parser)
	{
		global $smcFunc;

		// Set everything
		$this->settings = $settings;
		$this->text = $text;
		$this->tools = $tools;
		$this->parser = $parser;

		$this->_smcFunc = $smcFunc;

		$this->_tables = array(
			'status' => array(
				'name' => 'status',
				'table' => 'breeze_status',
				'property' => '_status',
				'columns' => array('status_id', 'status_owner_id', 'status_poster_id', 'status_time', 'status_body'),
				),
			'comments' => array(
				'name' => 'comments',
				'table' => 'breeze_comments',
				'property' => '_comments',
				'columns' => array('comments_id', 'comments_status_id', 'comments_status_owner_id', 'comments_poster_id', 'comments_profile_owner_id', 'comments_time', 'comments_body'),
				),
			'members' => array(
				'name' => 'members',
				'table' => 'members',
				'property' => '_members',
				'columns' => array('breeze_profile_views'),
				),
			'noti' => array(
				'name' => 'noti',
				'table' => 'breeze_notifications',
				'property' => '_noti',
				'columns' => array('id', 'sender', 'receiver', 'type', 'time', 'viewed', 'content'),
				),
		);
	}

	/**
	 * BreezeQuery::quickQuery()
	 *
	 * @param array $params An array with all the params  for the query
	 * @param array $data An array to pass to $smcFunc casting array
	 * @param bool $key A boolean value to asign a row as key on the returning array
	 * @param bool $single A bool to tell the query to return a single value instead of An array
	 * @return mixed either An array or a var with the query result
	 */
	public function quickQuery($params, $data, $key = false, $single = false)
	{
		$dataResult = array();

		$query = $this->_smcFunc['db_query']('', '
			SELECT ' . $params['rows'] .'
			FROM {db_prefix}' . $params['table'] .'
			'. (!empty($params['join']) ? 'LEFT JOIN '. $params['join'] : '') .'
			'. (!empty($params['where']) ? 'WHERE '. $params['where'] : '') .'
				'. (!empty($params['and']) ? 'AND '. $params['and'] : '') .'
				'. (!empty($params['andTwo']) ? 'AND '. $params['andTwo'] : '') .'
			'. (!empty($params['order']) ? 'ORDER BY ' . $params['order'] : '') .'
			'. (!empty($params['limit']) ? 'LIMIT '. $params['limit'] : '') . '',
			$data
		);

		if (!empty($single))
			while ($row = $this->_smcFunc['db_fetch_assoc']($query))
				$dataResult = $row;

		elseif (!empty($key) && empty($single))
			while ($row = $this->_smcFunc['db_fetch_assoc']($query))
				$dataResult[$row[$key]] = $row;

		elseif (empty($single) && empty($key))
			while ($row = $this->_smcFunc['db_fetch_assoc']($query))
				$dataResult[] = $row;

		$this->_smcFunc['db_free_result']($query);

		return $dataResult;
	}

	/**
	 * BreezeQuery::killCache()
	 *
	 * Disclaimer: Killing in breeze world means replace the existing cache data with a null value so SMF generates a new cache...
	 * @param string $type the name of value(s) to be deleted
	 * @return void
	 */
	public function killCache($type)
	{
		if (!is_array($type))
			$type = array($type);

		foreach ($type as $t)
			cache_put_data(Breeze::$name .'-'. $t, '');
	}

	/**
	 * BreezeQuery::resetTemp()
	 *
	 * Resets the temp property if you wish to use it, call this method first
	 * @return void
	 */
	protected function resetTemp()
	{
		$this->_temp = array();
	}

	/**
	 * BreezeQuery::getReturn()
	 *
	 * Return an associative array based on the entered params
	 * @param string $type The name of the table to fetch
	 * @param string $row The name of the row to fetch
	 * @param int $value The value to compare to
	 * @param bool $single true if the query will return only 1 array.
	 * @return array an associative array
	 */
	private function getReturn($type, $row, $value, $single = false)
	{
		// Cleaning
		$this->resetTemp();

		// Get the data
		$this->switchData($type);

		$return = '';

		// Do this only if there is something to work with
		if ($this->_temp)
		{
			// Generate An array with a defined key
			foreach ($this->_temp as $t)
			{
				if ($t[$row] == $value && !$single)
					$return[] = $t;

				// Get a single value
				else
					if ($t[$row] == $value && $single)
						$return = $t;
			}
		}

		// Cleaning
		$this->resetTemp();

		// Return the info we want as we want it
		return $return;
	}

	/**
	 * BreezeQuery::switchData()
	 *
	 * Set the temp array with the correct data acording to the type specified
	 * @param string $type the data type
	 * @return void
	 */
	private function switchData($type)
	{
		$property = $this->_tables[$type]['property'];
		$method = $this->_tables[$type]['name'];

		if (array_key_exists($type, $this->_tables))
			$this->_temp = $this->$property ? $this->$property : $this->$method();
	}

	/**
	 * BreezeQuery::getSingleValue()
	 *
	 * Needs a type, a row and a value, this iterates X array looking for X value in X row. Yes, this can be used to fetch more than one value if you really want to fetch more than 1 value.
	 * @param string $type the data type
	 * @param string $row the row where to fetch the value from, should be the actual row name in the array, not the row name in the DB.
	 * @param mixed $value  Most of the cases will be a int. the int is actually the ID of the particular value you are trying to fetch.
	 * @return array An array with the requested data
	 */
	public function getSingleValue($type, $row, $value)
	{
		// Cleaning
		$this->resetTemp();

		return $this->getReturn($type, $row, $value);
	}

	/**
	 * BreezeQuery::getLastStatus()
	 *
	 * It is not reliable to use the cache array for this one so let's do a query here.
	 * @see BreezeAjax
	 * @return array An array with the last status ID.
	 */
	public function getLastStatus()
	{
		$return = '';

		// Get the value directly from the DB
		$result = $this->_smcFunc['db_query']('', '
			SELECT status_id
			FROM {db_prefix}' . ($this->_tables['status']['table']) . '
			ORDER BY {raw:sort}
			LIMIT {int:limit}', array('sort' => 'status_id DESC', 'limit' => 1));

		while ($row = $this->_smcFunc['db_fetch_assoc']($result))
			$return = $row;

		$this->_smcFunc['db_free_result']($result);

		// Done?
		return $return;
	}

	/**
	 * BreezeQuery::getLastComment()
	 *
	 * Basically the same as the method above, query the DB to get the last comment added, ID only.
	 * @see BreezeAjax
	 * @return array An array with the last status ID.
	 */
	public function getLastComment()
	{
		$return = '';

		// Get the value directly from the DB
		$result = $this->_smcFunc['db_query']('', '
			SELECT comments_id
			FROM {db_prefix}' . ($this->_tables['comments']['table']) . '
			ORDER BY {raw:sort}
			LIMIT {int:limit}', array('sort' => 'comments_id DESC', 'limit' => 1));

		while ($row = $this->_smcFunc['db_fetch_assoc']($result))
			$return = $row;

		$this->_smcFunc['db_free_result']($result);

		// Done?
		return $return;
	}

	/**
	 * BreezeQuery::status()
	 *
	 * The main method to load all the status. This is one of the main queries.
	 * @return array a very big associative array with the status ID row as key
	 */
	protected function status()
	{
		// Use the cache please...
		if (($this->_status = cache_get_data(Breeze::$name .'-' . $this->_tables['status']['name'],
			120)) == null)
		{
			// Load all the status, set a limit if things get complicated
			$result = $this->_smcFunc['db_query']('', '
				SELECT '. implode(',', $this->_tables['status']['columns']) .'
				FROM {db_prefix}breeze_status
				' . ($this->settings->enable('admin_enable_limit') && $this->settings->
				enable('admin_limit_timeframe') ? 'WHERE status_time >= {int:status_time}':'') .
				'
				ORDER BY status_time DESC
				', array('status_time' => $this->settings->getSetting('admin_limit_timeframe'), ));

			// Populate the array like a boss!
			while ($row = $this->_smcFunc['db_fetch_assoc']($result))
				$this->_status[$row['status_id']] = array(
					'id' => $row['status_id'],
					'owner_id' => $row['status_owner_id'],
					'poster_id' => $row['status_poster_id'],
					'time' => $this->tools->timeElapsed($row['status_time']),
					'body' => $this->parser->display($row['status_body']),
					);

			$this->_smcFunc['db_free_result']($result);

			// Cache this beauty
			cache_put_data(Breeze::$name .'-' . $this->_tables['status']['name'], $this->_status, 120);
		}

		return $this->_status;
	}

	/**
	 * BreezeQuery::getStatus()
	 *
	 * Calls BreezeQuery::status() if the _status property doesn't exists already
	 * @return array An array with all the status loaded by status())
	 */
	public function getStatus()
	{
		return !empty($this->_status) ? $this->_status : $this->status();
	}

	/**
	 * BreezeQuery::getStatusByProfile()
	 *
	 * Get all status made in X profile page. Uses a custom query and store the results on separate cache entries per profile.
	 * @param int $id the ID of the user that owns the profile page, it does not matter who made that status as long as the status was made in X profile page.
	 * @return array An array containing all the status made in X profile page
	 */
	public function getStatusByProfile($id)
	{
		// Declare some generic vars, mainly to avoid errors
		$return = array(
			'data' => array(),
			'users' => array(),
		);

		// Use the cache please...
		if (($return = cache_get_data(Breeze::$name .'-' . $id, 120)) == null)
		{
			// Big query...
			$result = $this->_smcFunc['db_query']('', '
				SELECT s.status_id, s.status_owner_id, s.status_poster_id, s.status_time, s.status_body, c.comments_id, c.comments_status_id, c.comments_status_owner_id, comments_poster_id, c.comments_profile_owner_id, c.comments_time, c.comments_body
				FROM {db_prefix}breeze_status AS s
					LEFT JOIN {db_prefix}breeze_comments AS c ON (c.comments_status_id = s.status_id)
				WHERE s.status_owner_id = {int:owner}
				' . ($this->settings->enable('admin_enable_limit') && $this->settings->
				enable('admin_limit_timeframe') ? 'AND s.status_time >= {int:status_time}':'') .
				'
				ORDER BY s.status_time DESC
				', array('status_time' => $this->settings->getSetting('admin_limit_timeframe'),
					'owner' => $id));

			// Populate the array like a big heavy boss!
			while ($row = $this->_smcFunc['db_fetch_assoc']($result))
			{
				$return['data'][$row['status_id']] = array(
					'id' => $row['status_id'],
					'owner_id' => $row['status_owner_id'],
					'poster_id' => $row['status_poster_id'],
					'time' => $this->tools->timeElapsed($row['status_time']),
					'body' => $this->parser->display($row['status_body']),
					'comments' => array(),
				);

				// Comments
				if (!empty($row['comments_status_id']))
				{
					$c[$row['comments_status_id']][$row['comments_id']] = array(
						'id' => $row['comments_id'],
						'status_id' => $row['comments_status_id'],
						'status_owner_id' => $row['comments_status_owner_id'],
						'poster_id' => $row['comments_poster_id'],
						'profile_owner_id' => $row['comments_profile_owner_id'],
						'time' => $this->tools->timeElapsed($row['comments_time']),
						'body' => $this->parser->display($row['comments_body']),
					);

					// Merge them both
					$return['data'][$row['status_id']]['comments'] = $c[$row['comments_status_id']];
				}

				// Get the users IDs
				$return['users'][] = $row['comments_poster_id'];
				$return['users'][] = $row['status_owner_id'];
				$return['users'][] = $row['status_poster_id'];
			}

			$this->_smcFunc['db_free_result']($result);

			// Clean it a bit
			if (!empty($return['users']))
				$return['users'] = array_filter(array_unique($return['users']));

			// Cache this beauty
			cache_put_data(Breeze::$name .'-' . $id, $return, 120);
		}

		return $return;
	}

	/**
	 * BreezeQuery::getStatusByID()
	 *
	 * Get a single status based on the ID. This should return just one value, if it returns more, then we have a bug somewhere or you didn't provide a valid ID
	 * @see BreezeQuery::getReturn()
	 * @param int $id the ID of status you want to fetch.
	 * @access public
	 * @return array An array containing all the status made in X profile page
	 */
	public function getStatusByID($id, $user)
	{
		if (empty($id))
			return false;

		// Set some empty arrays to a void errors
		$return = array(
			'data' => array(),
			'users' => array(),
		);

		// For some reason we need to fetch the comments separately
		$c = array();

		$result = $this->_smcFunc['db_query']('', '
			SELECT s.status_id, s.status_owner_id, s.status_poster_id, s.status_time, s.status_body, c.comments_id, c.comments_status_id, c.comments_status_owner_id, comments_poster_id, c.comments_profile_owner_id, c.comments_time, c.comments_body
			FROM {db_prefix}breeze_status AS s
				LEFT JOIN {db_prefix}breeze_comments AS c ON (c.comments_status_id = s.status_id)
			WHERE s.status_id = {int:status_id}
			ORDER BY s.status_time DESC',
			array(
				'status_id' => $id
			)
		);

		// Populate the array like a big heavy boss!
		while ($row = $this->_smcFunc['db_fetch_assoc']($result))
		{
			$return['data'] = array(
				'id' => $row['status_id'],
				'owner_id' => $row['status_owner_id'],
				'poster_id' => $row['status_poster_id'],
				'time' => $this->tools->timeElapsed($row['status_time']),
				'body' => $this->parser->display($row['status_body']),
			);

			// Comments
			if (!empty($row['comments_status_id']))
			{
				$c[$row['comments_id']] = array(
					'id' => $row['comments_id'],
					'status_id' => $row['comments_status_id'],
					'status_owner_id' => $row['comments_status_owner_id'],
					'poster_id' => $row['comments_poster_id'],
					'profile_owner_id' => $row['comments_profile_owner_id'],
					'time' => $this->tools->timeElapsed($row['comments_time']),
					'body' => $this->parser->display($row['comments_body']),
				);

				// Merge them both
				$return['data']['comments'] = $c;
			}

			// Get the users IDs
			if (!empty($row['comments_poster_id']))
				$return['users'][] = $row['comments_poster_id'];

			$return['users'][] = $row['status_owner_id'];
			$return['users'][] = $row['status_poster_id'];
		}

		$this->_smcFunc['db_free_result']($result);

		// Clean it a bit
		$return['users'] = array_filter(array_unique($return['users']));

		return $return;
	}

	/**
	 * BreezeQuery::getStatusByUser()
	 *
	 * Get all status made by X user. This returns all the status made by x user, it does not matter on what profile page they were made.
	 * @see BreezeQuery::getReturn()
	 * @param int $id the ID of the user that you want to fetch the status from.
	 * @return array An array containing all the status made in X profile page
	 */
	public function getStatusByUser($id)
	{
		return $this->getReturn('status', 'status_user', $id);
	}

	/**
	 * BreezeQuery::getStatusByLast()
	 *
	 * Get the latest Status in the Status array. This returns the last status added to the array.
	 * @return array the last status added to the Status array
	 */
	public function getStatusByLast()
	{
		$array = $this->_status ? $this->_status:$this->status();

		return array_shift(array_values($array));
	}

	// Methods for comments

	/**
	 * BreezeQuery::comments()
	 *
	 * The main method to load all the comments. This is one of the main queries, load all the commments from all users.
	 * @return array a very big associative array with the comment ID as key
	 */
	protected function comments()
	{
		// Use the cache please...
		if (($this->_comments = cache_get_data(Breeze::$name .'-' . $this->_tables['comments']['name'],
			120)) == null)
		{
			// Load all the comments, set a limit if things get complicated
			$result = $this->_smcFunc['db_query']('', '
				SELECT '. implode(',', $this->_tables['comments']['columns']) .'
				FROM {db_prefix}breeze_comments
				' . ($this->settings->enable('admin_enable_limit') && $this->settings->
				enable('admin_limit_timeframe') ? 'WHERE comments_time >= {int:comments_time}':
				'') . '
				ORDER BY comments_time ASC
				', array('comments_time' => $this->settings->getSetting('admin_limit_timeframe'), ));

			// Populate the array like a comments boss!
			while ($row = $this->_smcFunc['db_fetch_assoc']($result))
			{
				$this->_comments[$row['comments_id']] = array(
					'id' => $row['comments_id'],
					'status_id' => $row['comments_status_id'],
					'status_owner_id' => $row['comments_status_owner_id'],
					'poster_id' => $row['comments_poster_id'],
					'profile_owner_id' => $row['comments_profile_owner_id'],
					'time' => $this->tools->timeElapsed($row['comments_time']),
					'body' => $this->parser->display($row['comments_body']),
					);
			}

			$this->_smcFunc['db_free_result']($result);

			// Cache this beauty
			cache_put_data(Breeze::$name .'-' . $this->_tables['comments']['name'], $this->_comments,
				120);
		}

		return $this->_comments;
	}

	/**
	 * BreezeQuery::getComments()
	 *
	 * Returns all comments in the comments array
	 * @return array An array containing all comments. ID as the key.
	 */
	public function getComments()
	{
		return !empty($this->_comments) ? $this->_comments : $this->comments();
	}

	/**
	 * BreezeQuery::getCommentsByProfile()
	 *
	 * Get all comments made in X profile page. Uses the generic method getReturn.
	 * @param int $id
	 * @return array An array containing all comments made in X profile page
	 */
	public function getCommentsByProfile($id)
	{
		return $this->getReturn($this->_tables['comments']['name'], 'profile_owner_id',
			$id);
	}

	/**
	 * BreezeQuery::getCommentsByStatus()
	 *
	 * Get all comments under an specific status
	 * @see BreezeQuery::getReturn()
	 * @param int $id
	 * @return
	 */
	public function getCommentsByStatus($id)
	{
		// Do not call the Comments method for every status, use it only once
		$temp = array();
		$temp2 = array();
		$comments = $this->getComments();

		if (!empty($comments))
		{
			$temp = $comments;

			foreach ($temp as $c)
				if ($c['status_id'] == $id)
					$temp2[$c['id']] = $c;
		}

		return $temp2;
	}

	// Editing methods

	/**
	 * BreezeQuery::insertStatus()
	 *
	 * @param mixed $array
	 * @return
	 */
	public function insertStatus($array)
	{
		// We don't need this no more
		cache_put_data(Breeze::$name .'-' . $array['owner_id'], '');

		// Insert!
		$this->_smcFunc['db_insert']('replace', '{db_prefix}' . ($this->_tables['status']['table']) .
			'', array(
			'status_owner_id' => 'int',
			'status_poster_id' => 'int',
			'status_time' => 'int',
			'status_body' => 'string',
			), $array, array('status_id', ));
	}

	/**
	 * BreezeQuery::insertComment()
	 *
	 * @param mixed $array
	 * @return
	 */
	public function insertComment($array)
	{
		// We don't need this no more
		$this->killCache($this->_tables['comments']['name']);

		// Insert!
		$this->_smcFunc['db_insert']('replace', '{db_prefix}' . ($this->_tables['comments']['table']) .
			'', array(
			'comments_status_id' => 'int',
			'comments_status_owner_id' => 'int',
			'comments_poster_id' => 'int',
			'comments_profile_owner_id' => 'int',
			'comments_time' => 'int',
			'comments_body' => 'string',
			), $array, array('comments_id', ));
	}

	/**
	 * BreezeQuery::deleteStatus()
	 *
	 * @param int $id
	 * @return
	 */
	public function deleteStatus($id)
	{
		// We don't need this no more
		$this->killCache($this->_tables['status']['name']);

		// Ladies first
		$this->deleteCommentByStatusID($id);

		// Same for status
		$this->_smcFunc['db_query']('', '
			DELETE FROM {db_prefix}' . ($this->_tables['status']['table']) . '
			WHERE status_id = {int:id}', array('id' => $id, ));
	}

	/**
	 * BreezeQuery::deleteCommentByStatusID()
	 *
	 * @param int $id
	 * @return
	 */
	public function deleteCommentByStatusID($id)
	{
		$this->_smcFunc['db_query']('', '
			DELETE FROM {db_prefix}' . ($this->_tables['comments']['table']) . '
			WHERE comments_status_id = {int:id}', array('id' => $id, ));
	}

	/**
	 * BreezeQuery::deleteComment()
	 *
	 * @param int $id
	 * @return
	 */
	public function deleteComment($id)
	{
		// Delete!
		$this->_smcFunc['db_query']('', '
			DELETE FROM {db_prefix}' . ($this->_tables['comments']['table']) . '
			WHERE comments_id = {int:id}',
			array(
				'id' => (int) $id,
			)
		);
	}

	/**
	 * BreezeQuery::members()
	 *
	 * @return
	 */
	protected function members()
	{
		// Use the cache please...
		if (($this->_members = cache_get_data(Breeze::$name .'-' . $this->_tables['members']['name'], 120)) == null)
		{
			// Load all the settings from all users
			$result = $this->_smcFunc['db_query']('', '
				SELECT pm_ignore_list, id_member
				FROM {db_prefix}' . $this->_tables['members']['table'] . '
				', array());

			// Populate the array like a boss!
			while ($row = $this->_smcFunc['db_fetch_assoc']($result))
				$this->_members[$row['id_member']] = $row;

			$this->_smcFunc['db_free_result']($result);

			// Cache this beauty
			cache_put_data(Breeze::$name .'-' . $this->_tables['members']['name'], $this->_members, 120);
		}

		return $this->_members;
	}

	/**
	 * BreezeQuery::getUserSetting()
	 *
	 * Gets a unique user setting
	 * @param int $user
	 * @param bool $setting
	 * @return bool|mixed either a boolean false or the requested value which can be a string or a boolean
	 */
	public function getUserSetting($user, $setting = false)
	{
		$return = $this->_members ? $this->_members:$this->members();

		if ($setting)
		{
			if (!empty($return[$user][$setting]))
				return $return[$user][$setting];

			else
				return false;
		}

		else
		{
			if (!empty($return[$user]))
				return $return[$user];

			else
				return false;
		}
	}

	/**
	 * BreezeQuery::noti()
	 *
	 * Loads all the notifications, uses cache when possible
	 * @return array
	 */
	protected function noti()
	{
		// Use the cache please...
		if (($this->_noti = cache_get_data(Breeze::$name .'-' . $this->_tables['noti']['name'],
			120)) == null)
		{
			$result = $this->_smcFunc['db_query']('', '
				SELECT '. implode(',', $this->_tables['noti']['columns']) .'
				FROM {db_prefix}' . $this->_tables['noti']['table'] . '
				', array()
			);

			// Populate the array like a boss!
			while ($row = $this->_smcFunc['db_fetch_assoc']($result))
				$this->_noti[$row['id']] = array(
					'id' => $row['id'],
					'sender' => $row['sender'],
					'receiver' => $row['receiver'],
					'type' => $row['type'],
					'time' => $row['time'],
					'viewed' => $row['viewed'],
					'content' => !empty($row['content']) ? json_decode($row['content'], true) : array(),
				);

			$this->_smcFunc['db_free_result']($result);

			// Cache this beauty
			cache_put_data(Breeze::$name .'-' . $this->_tables['noti']['name'], $this->_noti, 120);
		}

		return $this->_noti;
	}

	/**
	 * BreezeQuery::getNotifications()
	 *
	 * Calls BreezeQuery::noti() if its corresponding property is empty
	 * @see BreezeQuery::noti()
	 * @return bool|array Either an boolean false or An array of data
	 */
	public function getNotifications()
	{
		return !empty($this->_noti) ? $this->_noti : $this->noti();
	}

	public function getSingleNoti($values, $type)
	{
		$return = array();

		if (empty($values) || !is_array($values) || empty($type))
			return false;

		$result = $this->_smcFunc['db_query']('', '
			SELECT '. implode(',', $this->_tables['noti']['columns']) .'
			FROM {db_prefix}' . $this->_tables['noti']['table'] . '
			WHERE receiver = {int:receiver}
				AND sender = {int:sender}
				AND type = {string:type}
			LIMIT 1',
			array(
				'receiver' => $values['receiver'],
				'sender' => $values['sender'],
				'type' => $type,
			)
		);

		while ($row = $this->_smcFunc['db_fetch_assoc']($result))
			$return = $row;

		$this->_smcFunc['db_free_result']($result);

		return $return;
	}

	/**
	 * BreezeQuery::insertNotification()
	 *
	 * Inserts a notification entry on the DB
	 * @param array $array
	 * @return void
	 */
	public function insertNotification($array)
	{
		// We don't need this no more
		$this->killCache($this->_tables['noti']['name']);

		$this->_smcFunc['db_insert']('replace', '{db_prefix}' . ($this->_tables['noti']['table']) .
			'', array(
			'sender' => 'int',
			'receiver' => 'int',
			'type' => 'string',
			'time' => 'int',
			'viewed' => 'int',
			'content' => 'string',
			), $array, array('id'));
	}

	/**
	 * BreezeQuery::markNoti()
	 *
	 * Marks the specific notification entry as either read/unread 1 = read, 0 = unread
	 * @param int $id The notification ID
	 * @return
	 */
	public function markNoti($id, $user, $viewed)
	{
		// We don't need this no more
		$this->killCache($this->_tables['noti']['name'] . '-'. $user);

		// We actually want to change the value... Just invert the value, ugly, but it gets the job done
		$change = $viewed == 1 ? 0 : 1;

		// Mark as viewed
		$this->_smcFunc['db_query']('', '
			UPDATE {db_prefix}' . ($this->_tables['noti']['table']) . '
			SET viewed = {int:viewed}
			WHERE id = {int:id}',
			array(
				'viewed' => $change,
				'id' => $id,
			)
		);
	}

	/**
	 * BreezeQuery::deleteNoti()
	 *
	 * Deletes the specific notification entry from the DB
	 * @param int $id the notification ID
	 * @return void
	 */
	public function deleteNoti($id, $user)
	{
		// We don't need this no more
		$this->killCache($this->_tables['noti']['name'] . '-'. $user);

		// Delete!
		$this->_smcFunc['db_query']('', '
			DELETE
			FROM {db_prefix}' . ($this->_tables['noti']['table']) . '
			WHERE id = {int:id}',
			array(
				'id' => (int) $id
			)
		);
	}

	/**
	 * BreezeQuery::getNotificationByUser()
	 *
	 * @param int $user The user from where the notifications will be fetched
	 * @return array
	 */
	public function getNotificationByUser($user, $all = false)
	{
		// Use the cache please...
		if (($return = cache_get_data(Breeze::$name .'-' . $this->_tables['noti']['name'] . '-'. $user, 120)) == null)
		{
			/* There is no notifications */
			$return['users'] = array();
			$return['data'] = array();

			$result = $this->_smcFunc['db_query']('', '
				SELECT '. implode(',', $this->_tables['noti']['columns']) .'
				FROM {db_prefix}' . $this->_tables['noti']['table'] . '
				WHERE receiver = {int:receiver}
				'. (empty($all) ? 'AND viewed = 0' : '') .'
				', array(
					'receiver' => (int) $user,
				)
			);

			// Populate the array like a boss!
			while ($row = $this->_smcFunc['db_fetch_assoc']($result))
				$return['data'][$row['id']] = array(
					'id' => $row['id'],
					'sender' => $row['sender'],
					'receiver' => $row['receiver'],
					'type' => $row['type'],
					'time' => $row['time'],
					'viewed' => $row['viewed'],
					'content' => !empty($row['content']) ? json_decode($row['content'], true) : array(),
				);


			$this->_smcFunc['db_free_result']($result);

			// Delete duplicate IDs
			$return['users'] = array_filter(array_unique($return['users']));

			// Cache this beauty for the most used stream feature
			if (empty($all))
				cache_put_data(Breeze::$name .'-' . $this->_tables['noti']['name'] . '-'. $user, $return, 120);
		}

		return $return;
	}

	/**
	 * BreezeQuery::getNotificationByUserSender()
	 *
	 * @see BreezeQuery::getReturn()
	 * @param int $user The user from where the notifications will be fetched
	 * @return array
	 */
	public function getNotificationByUserSender($user)
	{
		return $this->getReturn($this->_tables['noti']['name'], 'user', $user);
	}

	/**
	 * BreezeQuery::getNotificationByType()
	 *
	 * Gets all the notifications stored under an specific type
	 * @param string $type the notification type
	 * @param bool $user
	 * @return bool|array Either An array with data or a boolean false
	 */
	public function getNotificationByType($type, $user = false)
	{
		global $context;

		$return = $this->getReturn($this->_tables['noti']['name'], 'type', $type);
		$returnUser = array();
		$this->test = array();

		if (!empty($return))
		{
			// Lets return the request for a particular user
			if ($user)
			{
				foreach ($return as $r)
					if ($r['receiver'] == $user)
					{
						$returnUser[$r['id']] = $r;

						// load the user's link
						$this->tools->loadUserInfo($r['user']);

						// build the message
						$returnUser[$r['id']]['content']['message'] = sprintf($this->text->getText('buddy_messagerequest_message'),
							$context['Breeze']['user_info'][$r['user']]['link'], $r['id']);
					}

				return $returnUser;
			}

			// No? then send the entire array
			else
				return $return;
		}

		else
			return false;
	}

	/**
	 * BreezeQuery::updateProfileViews()
	 *
	 * Updates the member profile views count
	 * @param int $id the user ID
	 * @param string $json_string a json string
	 */
	public function updateProfileViews($id, $json_string)
	{
		// Do not waste my time
		if (empty($id) || empty($json_string))
			return false;

		$this->_smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET breeze_profile_views = {string:json_string}
			WHERE id_member = ({int:id})',
			array(
				'id' => (int) $id,
				'json_string' => $json_string,
			)
		);
	}

	public function getViews($user)
	{
		$result = $this->_smcFunc['db_query']('', '
			SELECT breeze_profile_views
			FROM {db_prefix}' . $this->_tables['members']['table'] . '
			WHERE id_member = {int:user}
			', array(
				'user' => (int) $user,
			)
		);

		// Populate the array like a boss!
		while ($row = $this->_smcFunc['db_fetch_row']($result))
			$return = $row[0];

		$this->_smcFunc['db_free_result']($result);

		// Return the data
		return $return;
	}

	/**
	 * BreezeQuery::deletevLog()
	 *
	 * Deletes the specific visits log entry from the DB
	 * @param int $user the user ID
	 * @return void
	 */
	public function deleteViews($user)
	{
		// Delete!
		$this->_smcFunc['db_query']('', '
			UPDATE {db_prefix}' . $this->_tables['members']['table'] . '
			SET breeze_profile_views = {string:empty}
			WHERE id_member = {int:id}',
			array(
				'id' => (int) $user,
				'empty' => ''
			)
		);
	}

	public function userMention()
	{
		global $smcFunc;

		// Let us set a very long-lived cache entry
		if (($return = cache_get_data(Breeze::$name .'-Mentions', 7200)) == null)
		{
			$return = array();
			$postsLimit = $this->settings->enable('admin_posts_for_mention') ? (int) $this->settings->getSetting('admin_posts_for_mention') : 1;

			$result = $smcFunc['db_query']('', '
				SELECT id_member, member_name, real_name
				FROM {db_prefix}members
				' . ($this->settings->enable('admin_posts_for_mention') ? '
				WHERE posts >= {int:p}' : '') .
				'',array(
					'p' => $postsLimit,
				)
			);

			while ($row = $smcFunc['db_fetch_assoc']($result))
				$return[] = array(
					'name' => $row['real_name'],
					'id' => (int) $row['id_member'],
				);

			$smcFunc['db_free_result']($result);

			// Cached and forget about it
			cache_put_data(Breeze::$name .'-Mentions', $return, 7200);
		}

		return $return;
	}
}

/*
* Saturday is almost over
* getting high on stormy weather
* something to remember
* a sunny day can make you crumble.
*/
