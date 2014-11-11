<?php

/**
 * BreezeQuery
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Class BreezeQuery
 */
class BreezeQuery
{
	/**
	 * @var array
	 */
	protected $_status = array();
	protected $_noti = array();
	protected $_comments = array();
	protected $_members = array();
	protected $_userLikes = array();

	/**
	 * @var object
	 */
	protected $_app;

	/**
	 * BreezeQuery::__construct()
	 *
	 * Creates the needed properties.
	 */
	public function __construct($app)
	{
		// Set everything
		$this->_app = $app;

		$this->_tables = array(
			'options' => array(
				'name' => 'options',
				'table' => 'breeze_options',
				'property' => '_options',
				'columns' => array('variable', 'value',),
				),
			'status' => array(
				'name' => 'status',
				'table' => 'breeze_status',
				'property' => '_status',
				'columns' => array('status_id', 'status_owner_id', 'status_poster_id', 'status_time', 'status_body', 'likes',),
				),
			'comments' => array(
				'name' => 'comments',
				'table' => 'breeze_comments',
				'property' => '_comments',
				'columns' => array('comments_id', 'comments_status_id', 'comments_status_owner_id', 'comments_poster_id', 'comments_profile_id', 'comments_time', 'comments_body', 'likes'),
				),
			'members' => array(
				'name' => 'members',
				'table' => 'members',
				'property' => '_members',
				'columns' => array('breeze_profile_views', 'pm_ignore_list', 'buddy_list',),
				),
			'likes' => array(
				'name' => 'likes',
				'table' => 'user_likes',
				'property' => '_likes',
				'columns' => array('id_member', 'content_type', 'content_id', 'like_time',),
				),
			'moods' => array(
				'name' => 'moods',
				'table' => 'breeze_moods',
				'property' => '_noti',
				'columns' => array('moods_id', 'name', 'file', 'ext', 'description', 'enable',),
				),
			'alerts' => array(
				'name' => 'alerts',
				'table' => 'user_alerts',
				'property' => '_alerts',
				'columns' => array('id_alert', 'alert_time', 'id_member', 'id_member_started', 'member_name', 'content_type', 'content_id', 'content_action', 'is_read', 'extra'),
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

		$query = $this->_app['tools']->smcFunc['db_query']('', '
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
			while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($query))
				$dataResult = $row;

		elseif (!empty($key) && empty($single))
			while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($query))
				$dataResult[$row[$key]] = $row;

		elseif (empty($single) && empty($key))
			while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($query))
				$dataResult[] = $row;

		$this->_app['tools']->smcFunc['db_free_result']($query);

		return $dataResult;
	}

	/**
	 * BreezeQuery::killCache()
	 *
	 * Disclaimer: Killing in breeze world means replace the existing cache data with a null value so SMF generates a new cache...
	 * With the data provided, we need to clean the main cache entry, which is the per profile cache
	 * @param string $type the name of value(s) to be deleted
	 * @return void
	 */
	public function killCache($type, $id, $profile_owner = false)
	{
		// If we didn't get a profile owner, lets get it from the data provided...
		if (!$profile_owner)
		{
			$columnName = ($type == 'comments' ? 'comments_profile' : 'status') . '_owner_id';

			$result = $this->_app['tools']->smcFunc['db_query']('', '
				SELECT '. ($columnName) .'
				FROM {db_prefix}breeze_'. ($type) .'
				WHERE '. ($type) .'_id = {int:id}
				', array('id' => $id,));

			while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($result))
				$profile_owner = $row[$columnName];

			$this->_app['tools']->smcFunc['db_free_result']($result);
		}

		if (empty($profile_owner))
			return false;

		// We got the data we need, turn it into an array
		$profile_owner = (array) $profile_owner;

		foreach ($profile_owner as $owner)
			cache_put_data(Breeze::$name .'-Profile-'. $owner, '');

		// Clean any other cache too
	}

	/**
	 * BreezeQuery::getCount()
	 *
	 * Gets and return the number of rows from the data provided
	 * Only works for the status table.
	 * @param mixed $data either a single ID or an array of IDs to match the query against.
	 * @param string $column The column name to check the status from, needs to be a column that stores users IDs.
	 * @return array
	 */
	protected function getCount($data, $column)
	{
		$count = 0;

		if (empty($data) || empty($column))
			return $count;

		// Gotta make sure we have and array of integers.
		$data = array_map('intval', (array) $data);

		$result = $this->_app['tools']->smcFunc['db_query']('', '
			SELECT status_id
			FROM {db_prefix}breeze_status
			WHERE '. ($column) .' IN ({array_int:data})',
			array(
				'data' => $data
			)
		);

		$count =  $this->_app['tools']->smcFunc['db_num_rows']($result);

		$this->_app['tools']->smcFunc['db_free_result']($result);

		return $count;
	}

	/**
	 * BreezeQuery::getSingleValue()
	 *
	 * Needs a type, a row and a value, this queries X looking for Y value in Z row. Yes, this can be used to fetch more than one value if you really want to fetch more than 1 value.
	 * @param string $type the data type
	 * @param string $row the row where to fetch the value from.
	 * @param mixed $value  Most of the cases will be a int. the int is actually the ID of the particular value you are trying to fetch.
	 * @param boolean $raw If false, the data from the request will be passed to BreezeQuery::generateData() to further process it.
	 * @return array An array with the requested data
	 */
	public function getSingleValue($type, $row, $value, $raw = false)
	{
		// The usual checks
		if (empty($type) || empty($row) || empty($value))
			return false;

		// Get the value directly from the DB
		$result = $this->_app['tools']->smcFunc['db_query']('', '
			SELECT '. implode(', ', $this->_tables[$type]['columns']) .'
			FROM {db_prefix}' . ($this->_tables[$type]['table']) . '
			WHERE '. ($row) .' = '. ($value) .'
			', array()
		);

		while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($result))
			$return = $raw ? $row : $this->generateData($row, $type);

		$this->_app['tools']->smcFunc['db_free_result']($result);

		// Done?
		return !empty($return) ? $return : false;
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
		$result = $this->_app['tools']->smcFunc['db_query']('', '
			SELECT '. implode(', ', $this->_tables['status']['columns']) .'
			FROM {db_prefix}' . ($this->_tables['status']['table']) . '
			ORDER BY {raw:sort}
			LIMIT {int:limit}', array('sort' => 'status_id DESC', 'limit' => 1));

		while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($result))
			$return = $this->generateData($row, 'status');

		$this->_app['tools']->smcFunc['db_free_result']($result);

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
		$result = $this->_app['tools']->smcFunc['db_query']('', '
			SELECT '. implode(', ', $this->_tables['comments']['columns']) .'
			FROM {db_prefix}' . ($this->_tables['comments']['table']) . '
			ORDER BY {raw:sort}
			LIMIT {int:limit}', array('sort' => 'comments_id DESC', 'limit' => 1));

		while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($result))
			$return = $this->generateData($row, 'comments');

		$this->_app['tools']->smcFunc['db_free_result']($result);

		// Done?
		return $return;
	}

	/**
	 * BreezeQuery::getStatus()
	 *
	 * Get all status made by X users. no pagination, no other special code.
	 * @param int|array $id the ID(s) of the user(s) to get the status from. If left empty the functon will load all available status.
	 * @param boolean $getComments Whether or not to include comments made on each status.
	 * @param int $limit How many status do you want to retrieve.
	 * @param string $sort Sorting for retrieving the data, by default is set to DESC (most recent)
	 * @return array An array containing all the status made by those users, two keys, data and users
	 */
	public function getStatus($id = false, $getComments = false, $limit = 5, $sort = 'status_id DESC')
	{
		// Declare some generic vars, mainly to avoid errors
		$return = array(
			'data' => array(),
			'users' => array(),
		);

		$statusIDs = array();

		$id = !empty($id) ? (array) $id : false;

		// Fetch the status.
		$result = $this->_app['tools']->smcFunc['db_query']('', '
			SELECT '. implode(', ', $this->_tables['status']['columns']) .'
			FROM {db_prefix}'. ($this->_tables['status']['table']) .'
			'. (!empty($id) ? 'WHERE status_owner_id IN({array_int:owner})' : '') .'
			ORDER BY {raw:sort}
			LIMIT {int:limit}',
			array(
				'owner' => $id,
				'sort' => $sort,
				'limit' => $limit,
			)
		);

		// Populate the array like a big heavy boss!
		while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($result))
		{
			// Are we also fetching comments?
			if ($getComments)
				$statusIDs[] = $row['status_id'];

			$return['data'][$row['status_id']] = $this->generateData($row, 'status');

			// Get the users IDs
			$return['users'][] = $row['status_owner_id'];
			$return['users'][] = $row['status_poster_id'];
		}

		$this->_app['tools']->smcFunc['db_free_result']($result);

		// Now get the comments for each status.
		if ($getComments && !empty($statusIDs))
		{
			$result = $this->_app['tools']->smcFunc['db_query']('', '
				SELECT '. implode(', ', $this->_tables['comments']['columns']) .'
				FROM {db_prefix}'. ($this->_tables['comments']['table']) .'
				WHERE comments_status_id IN({array_int:status})
				ORDER BY comments_id ASC
				',
				array(
					'status' => $statusIDs,
				)
			);

			// Append the data to our main return array
			while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($result))
			{
				$return['data'][$row['comments_status_id']]['comments'][$row['comments_id']] = $this->generateData($row, 'comments');

				// Append the users IDs.
				$return['users'][] = $row['comments_poster_id'];
			}

			$this->_app['tools']->smcFunc['db_free_result']($result);
		}

		// Clean it a bit
		if (!empty($return['users']))
			$return['users'] = array_values(array_filter(array_unique($return['users'])));

		return $return;
	}

	/**
	 * BreezeQuery::getStatusByProfile()
	 *
	 * Get all status made in X profile page. Uses a custom query per profile.
	 * @param int $id the ID of the user that owns the profile page, it does not matter who made that status as long as the status was made in X profile page.
	 * @return array An array containing all the status made in X profile page
	 */
	public function getStatusByProfile($id, $maxIndex, $start)
	{
		// Declare some generic vars, mainly to avoid errors
		$return = array(
			'data' => array(),
			'users' => array(),
			'pagination' => '',
			'count' => 0
		);

		$statusIDs = array();

		// Not worth the effort...
		if (empty($id) || empty($maxIndex))
			return $return;

		// How many precious little gems do we have?
		$count = $this->getCount($id, 'status_owner_id');

		// Fetch the status.
		$result = $this->_app['tools']->smcFunc['db_query']('', '
			SELECT '. (implode(', ', $this->_tables['status']['columns'])) .'
			FROM {db_prefix}'. ($this->_tables['status']['table']) .'
			WHERE status_owner_id = {int:owner}
			ORDER BY status_id DESC
			LIMIT {int:start}, {int:maxindex}
			',
			array(
				'start' => $start,
				'maxindex' => $maxIndex,
				'owner' => $id
			)
		);

		// Populate the array like a big heavy boss!
		while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($result))
		{
			// First things first, collect the status IDs
			$statusIDs[] = $row['status_id'];

			$return['data'][$row['status_id']] = $this->generateData($row, 'status');

			// Get the users IDs
			$return['users'][] = $row['status_owner_id'];
			$return['users'][] = $row['status_poster_id'];
		}

		$this->_app['tools']->smcFunc['db_free_result']($result);

		// Now get the comments for each status.
		if (!empty($statusIDs))
		{
			$result = $this->_app['tools']->smcFunc['db_query']('', '
				SELECT '. implode(', ', $this->_tables['comments']['columns']) .'
				FROM {db_prefix}'. ($this->_tables['comments']['table']) .'
				WHERE comments_status_id IN({array_int:status})
				ORDER BY comments_id ASC
				',
				array(
					'status' => $statusIDs,
				)
			);

			// Append the data to our main return array
			while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($result))
			{
				$return['data'][$row['comments_status_id']]['comments'][$row['comments_id']] = $this->generateData($row, 'comments');

				// Append the users IDs.
				$return['users'][] = $row['comments_poster_id'];
			}

			$this->_app['tools']->smcFunc['db_free_result']($result);
		}

		// Clean it a bit
		if (!empty($return['users']))
			$return['users'] = array_filter(array_unique($return['users']));

		// Lastly, build the pagination
		$return['pagination'] = constructPageIndex($this->_app['tools']->scriptUrl . '?action=profile;u='. $id, $start, $count, $maxIndex, false);

		// Pass the total amount of items
		$return['count'] = $count;

		return $return;
	}

	/**
	 * BreezeQuery::getStatusByID()
	 *
	 * Get a single status based on the ID. This should return just one value, if it returns more, then we have a bug somewhere or you didn't provide a valid ID
	 * @param int $id the ID of status you want to fetch.
	 * @access public
	 * @return array An array containing all the data related to the single ID given.
	 */
	public function getStatusByID($id)
	{
		if (empty($id))
			return false;

		// Set some empty arrays to a void errors
		$return = array(
			'data' => array(),
			'users' => array(),
		);

		$statusIDs =array();

		$result = $this->_app['tools']->smcFunc['db_query']('', '
			SELECT '. implode(', ', $this->_tables['status']['columns']) .'
			FROM {db_prefix}'. ($this->_tables['status']['table']) .'
			WHERE status_id = {int:status_id}',
			array(
				'status_id' => $id
			)
		);

		// Populate the array like a big heavy boss!
		while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($result))
		{
			// Get the Ids to fetch the comments.
			$statusIDs[] = $row['status_id'];

			$return['data'][$row['status_id']] = $this->generateData($row, 'status');

			$return['users'][] = $row['status_owner_id'];
			$return['users'][] = $row['status_poster_id'];
		}

		$this->_app['tools']->smcFunc['db_free_result']($result);

		// Now get the comments for each status.
		if (!empty($statusIDs))
		{
			$result = $this->_app['tools']->smcFunc['db_query']('', '
				SELECT '. implode(', ', $this->_tables['comments']['columns']) .'
				FROM {db_prefix}'. ($this->_tables['comments']['table']) .'
				WHERE comments_status_id IN({array_int:status})
				ORDER BY comments_id ASC
				',
				array(
					'status' => $statusIDs,
				)
			);

			// Append the data to our main return array
			while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($result))
			{
				$return['data'][$row['comments_status_id']]['comments'][$row['comments_id']] = $this->generateData($row, 'comments');

				// Append the users IDs.
				$return['users'][] = $row['comments_poster_id'];
			}

			$this->_app['tools']->smcFunc['db_free_result']($result);
		}

		// Clean it up!
		$return['users'] = array_filter(array_unique($return['users']));

		return $return;
	}

	/**
	 * BreezeQuery::getStatusByUser()
	 *
	 * Get all status made by X user, it does not matter on what profile page they were made.
	 * @param int|array $id the ID of the user(s) that you want to fetch the status from.
	 * @param int $maxIndex The maximum amount of status to fetch.
	 * @param int $currentPage For working alongside pagination.
	 * @return array An array containing all the status made in X profile page
	 */
	public function getStatusByUser($id, $maxIndex, $start)
	{
		if (empty($id))
			return false;

		// Set some empty arrays to a void errors
		$return = array(
			'data' => array(),
			'users' => array(),
			'pagination' => '',
			'count' => 0
		);

		$statusIDs = array();

		// Work with arrays
		$id = array_map('intval', (array) $id);

		// Count all the possible items we can fetch
		$count = $this->getCount($id, 'status_poster_id');

		// Get all the status.
		$result = $this->_app['tools']->smcFunc['db_query']('', '
			SELECT '. implode(', ', $this->_tables['status']['columns']) .'
			FROM {db_prefix}'. ($this->_tables['status']['table']) .'
			WHERE status_poster_id IN ({array_int:id})
			ORDER BY status_id DESC
			LIMIT {int:start}, {int:maxindex}',
			array(
				'start' => $start,
				'maxindex' => $maxIndex,
				'id' => $id
			)
		);

		// Populate the array like a big heavy boss!
		while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($result))
		{
			// Get the Ids to fetch the comments.
			$statusIDs[] = $row['status_id'];

			$return['data'][$row['status_id']] = $this->generateData($row, 'status');

			$return['users'][] = $row['status_owner_id'];
			$return['users'][] = $row['status_poster_id'];
		}

		$this->_app['tools']->smcFunc['db_free_result']($result);

		if (!empty($statusIDs))
		{
			$result = $this->_app['tools']->smcFunc['db_query']('', '
				SELECT '. implode(', ', $this->_tables['comments']['columns']) .'
				FROM {db_prefix}'. ($this->_tables['comments']['table']) .'
				WHERE comments_status_id IN({array_int:status})
				ORDER BY comments_id ASC',
				array(
					'status' => $statusIDs,
				)
			);

			// Append the data to our main return array
			while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($result))
			{
				$return['data'][$row['comments_status_id']]['comments'][$row['comments_id']] = $this->generateData($row, 'comments');

				// Append the users IDs.
				$return['users'][] = $row['comments_poster_id'];
			}

			$this->_app['tools']->smcFunc['db_free_result']($result);
		}

		// Clean it a bit
		$return['users'] = array_filter(array_unique($return['users']));

		// Lastly, build the pagination
		$return['pagination'] = constructPageIndex($this->_app['tools']->scriptUrl . '?action=wall', $start, $count, $maxIndex, false);

		// Pass the total amount of items
		$return['count'] = $count;

		return $return;
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
		// Insert!
		$this->_app['tools']->smcFunc['db_insert']('replace', '{db_prefix}' . ($this->_tables['status']['table']) .
			'', array(
			'status_owner_id' => 'int',
			'status_poster_id' => 'int',
			'status_time' => 'int',
			'status_body' => 'string',
			), $array, array('status_id', ));

		// Get the newly created status ID
		$status_id = $this->_app['tools']->smcFunc['db_insert_id']('{db_prefix}' . ($this->_tables['status']['table']), 'status_id');

		//Kill the profile cache
		$this->killCache('status', $status_id, $array['owner_id']);

		// Return the newly inserted status ID
		return $status_id;
	}

	/**
	 * BreezeQuery::insertComment()
	 *
	 * @param mixed $array
	 * @return
	 */
	public function insertComment($array)
	{
		// Insert!
		$this->_app['tools']->smcFunc['db_insert']('replace', '{db_prefix}' . ($this->_tables['comments']['table']) .
			'', array(
			'comments_status_id' => 'int',
			'comments_status_owner_id' => 'int',
			'comments_poster_id' => 'int',
			'comments_profile_id' => 'int',
			'comments_time' => 'int',
			'comments_body' => 'string',
			), $array, array('comments_id', ));

		// Get the newly created comment ID
		$comment_id = $this->_app['tools']->smcFunc['db_insert_id']('{db_prefix}' . ($this->_tables['comments']['table']), 'comments_id');

		//Kill the profile cache
		$this->killCache('comments', $comment_id, $array['profile_id']);

		// Return the newly inserted comment ID
		return $comment_id;
	}

	/**
	 * BreezeQuery::deleteStatus()
	 *
	 * @param int $id
	 * @return
	 */
	public function deleteStatus($id, $profile_owner = false)
	{
		// We know the profile_owner, pass it to avoid an extra query
		$this->killCache('status', $id, $profile_owner);

		// Ladies first
		$this->deleteCommentByStatusID($id);

		// Same for status
		$this->_app['tools']->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}' . ($this->_tables['status']['table']) . '
			WHERE status_id = {int:id}', array('id' => $id, ));
	}

	/**
	 * BreezeQuery::deleteCommentByStatusID()
	 *
	 * This shouldn't be called as a standalone method, use deleteComments() instead
	 * @param int $id
	 * @return
	 */
	public function deleteCommentByStatusID($id)
	{
		$this->_app['tools']->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}' . ($this->_tables['comments']['table']) . '
			WHERE comments_status_id = {int:id}', array('id' => $id, ));
	}

	/**
	 * BreezeQuery::deleteComments()
	 *
	 * @param int $id
	 * @return
	 */
	public function deleteComments($id, $profile_owner = false)
	{
		// If we know the profile_owner ID we will save an extra query so try to include it as a param please!
		$this->killCache('comments', $id, $profile_owner);

		// Delete!
		$this->_app['tools']->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}' . ($this->_tables['comments']['table']) . '
			WHERE comments_id = {int:id}',
			array(
				'id' => (int) $id,
			)
		);
	}

	/**
	 * BreezeQuery::getUserSetting()
	 *
	 * Gets a unique user setting
	 * @param int $user The user ID
	 * @return bool|array Either a boolean false or the requested user data.
	 */
	public function getUserSettings($user)
	{
		if (empty($user))
			return false;

		if (($return = cache_get_data(Breeze::$name .'-' . $this->_tables['options']['name'] .'-'. $user,
			120)) == null)
		{
			$return = array();

			$result = $this->_app['tools']->smcFunc['db_query']('', '
				SELECT op.' . (implode(', op.', $this->_tables['options']['columns'])) . ', mem.' . (implode(', mem.', $this->_tables['members']['columns'])) . '
				FROM {db_prefix}' . ($this->_tables['options']['table']) . ' AS op
					LEFT JOIN {db_prefix}'. ($this->_tables['members']['table']) .' AS mem ON (mem.id_member = {int:user})
				WHERE member_id = {int:user}',
				array(
					'user' => $user,
				)
			);

			// Populate the array like a boss!
			while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($result))
			{
				$return[$row['variable']] = is_numeric($row['value']) ? (int) $row['value'] : (string) $row['value'];

				// Special case for the cover image info.
				if ($row['variable'] == 'cover')
					$return[$row['variable']] = !empty($row['value']) ? json_decode($row['value'], true) : array();

				$return += array(
					// 'gender' => $row['gender'], @todo get the custom profile fields and merge them here
					'buddiesList' => !empty($row['buddy_list']) ? explode(',', $row['buddy_list']) : array(),
					'ignoredList' => $row['pm_ignore_list'],
					'profileViews' => $row['breeze_profile_views'],
				);
			}

			$this->_app['tools']->smcFunc['db_free_result']($result);

			// Cache this beauty.
			cache_put_data(Breeze::$name .'-' . $this->_tables['options']['name'] .'-'. $user, $return, 120);
		}

		return $return;
	}

	/**
	 * BreezeQuery::insertUserSettings()
	 *
	 * Creates a new set of user settings.
	 * @param
	 * @param
	 */
	public function insertUserSettings($array, $userID)
	{
		if (empty($array) || empty($userID))
			return false;

		cache_put_data(Breeze::$name .'-' . $this->_tables['options']['name'] .'-'. $userID, null, 120);

		$array = (array) $array;
		$userID = (int) $userID;
		$inserts = array();

		foreach ($array as $var => $val)
			$inserts[] = array($userID, $var, $val);

		if (!empty($inserts))
			$this->_app['tools']->smcFunc['db_insert']('replace',
				'{db_prefix}' . ($this->_tables['options']['table']),
				array('member_id' => 'int', 'variable' => 'string-255', 'value' => 'string-65534'),
				$inserts,
				array('member_id')
			);

		// Force getting the new settings.
		return $this->getUserSettings($userID);
	}

	public function createLog($params)
	{
		if (empty($params))
			return false;


	}

	public function getLog()
	{

	}

	/**
	 * BreezeQuery::insertNoti()
	 *
	 * Inserts all Breeze related background tasks.
	 * @param array $params An array of params, DOH!
	 * @params string $type a unique identifier.
	 * @return void
	 */
	public function insertNoti($params, $type)
	{
		if (empty($params) || empty($type))
			return false;

		// Gotta append a type so we can pretend to know what we're doing...
		$params['content_type'] = $type;

		$this->_app['tools']->smcFunc['db_insert']('insert',
			'{db_prefix}background_tasks',
			array('task_file' => 'string', 'task_class' => 'string', 'task_data' => 'string', 'claimed_time' => 'int'),
			array('$sourcedir/tasks/Breeze-Notify.php', 'Breeze_Notify_Background', serialize($params), 0),
			array('id_task')
		);
	}

	/**
	 * BreezeQuery::notiSpam()
	 *
	 * Give the data given, checks if an alert has already been add tot the DB.
	 * @param int $user The alert "receiver".
	 * @param string $type a unique identifier.
	 * @param int $id the unique ID for a given "action" if true, it means the mod will look for that very specific content, if not, the method will look for all contents.
	 * @param int $sender the person who fired up the alert, commonly know as the "sender".
	 * @return integer The ID if there is a match, 0 if there is none.
	 */
	public function notiSpam($user, $type, $id = false, $sender = false)
	{
		// No user? no type? no fun...
		if (empty($user) || empty($type))
			return true;

		$result = false;

		$type = Breeze::$txtpattern . $type;

		$request = $this->_app['tools']->smcFunc['db_query']('', '
			SELECT id_alert
			FROM {db_prefix}' . ($this->_tables['alerts']['table']) .'
			WHERE id_member = {int:id_member}
				AND is_read = 0
				AND content_type = {string:content_type}
				'. ($id ? 'AND content_id = {int:content_id}' : '') .'
				'. ($sender ? 'AND id_member_started = {int:sender}' : '') .'',
			array(
				'id_member' => $user,
				'content_type' => $type,
				'content_id' => $id,
				'sender' => $sender,
			)
		);

		list($result) = $this->_app['tools']->smcFunc['db_fetch_row']($request);

		$this->_app['tools']->smcFunc['db_free_result']($request);

		return (int) $result;
	}

	public function createAlert($params)
	{
		if (empty($params))
			return false;

		// Add the unique identifier.
		$params['content_type'] = Breeze::$txtpattern . $params['content_type'];

		$this->_app['tools']->smcFunc['db_insert']('insert', '{db_prefix}' . ($this->_tables['alerts']['table']) .'', array('alert_time' => 'int', 'id_member' => 'int', 'id_member_started' => 'int', 'member_name' => 'string', 'content_type' => 'string', 'content_id' => 'int', 'content_action' => 'string', 'is_read' => 'int', 'extra' => 'string'), $params, array('id_alert'));
	}

	/**
	 * BreezeQuery::updateAlert()
	 *
	 * Updates an existing user alert with new data
	 * @param array a column => new value array
	 * @param int|array a single ID or an array of IDs
	 * @return
	 */
	public function updateAlert($params, $id)
	{
		if ((empty($params) || !is_array($params)) || empty($id))
			return false;

		// Create a nice formatted string.
		$string = '';

		foreach ($params as $column => $newValue)
			$string .= $column .' = '. $newValue . ($newValue != end($params) ? ', ' : '');

		$this->_app['tools']->smcFunc['db_query']('', '
			UPDATE {db_prefix}' . ($this->_tables['alerts']['table']) . '
			SET '. ($string) .'
			WHERE id_alert '. (is_array($id) ? 'IN ({array_int:id})' : '= {int:id}'),
			array(
				'id' => $id,
			)
		);
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

		$this->_app['tools']->smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET breeze_profile_views = {string:json_string}
			WHERE id_member = ({int:id})',
			array(
				'id' => (int) $id,
				'json_string' => $json_string,
			)
		);
	}

	/**
	 * BreezeQuery::getViews()
	 *
	 * Gets the profile views
	 * @param integer $user The user ID
	 * @return string a json string.
	 */
	public function getViews($user)
	{
		$result = $this->_app['tools']->smcFunc['db_query']('', '
			SELECT breeze_profile_views
			FROM {db_prefix}' . $this->_tables['members']['table'] . '
			WHERE id_member = {int:user}
			', array(
				'user' => (int) $user,
			)
		);

		// Populate the array like a boss!
		while ($row = $this->_app['tools']->smcFunc['db_fetch_row']($result))
			$return = $row[0];

		$this->_app['tools']->smcFunc['db_free_result']($result);

		// Return the data
		return $return;
	}

	/**
	 * BreezeQuery::deleteViews()
	 *
	 * Deletes the specific visitors log entry from the DB
	 * @param int $user the user ID
	 * @return void
	 */
	public function deleteViews($user)
	{
		// Delete!
		$this->_app['tools']->smcFunc['db_query']('', '
			UPDATE {db_prefix}' . $this->_tables['members']['table'] . '
			SET breeze_profile_views = {string:empty}
			WHERE id_member = {int:id}',
			array(
				'id' => (int) $user,
				'empty' => ''
			)
		);
	}

	/**
	 * BreezeQuery::userMention()
	 *
	 * Gets a string and queries the DB looking for any possible matches, caches the result.
	 * @param string $match a 3 letter string
	 * @return array the matched IDs and names.
	 */
	public function userMention($match)
	{
		// By default we return these empty values
		$return = array(
			'name' => '',
			'id' => ''
		);

		// meh...
		if (empty($match))
			return $return;

		// Don't be naughty...
		$match = $this->_app['tools']->smcFunc['htmltrim']($this->_app['tools']->smcFunc['htmlspecialchars']($match), ENT_QUOTES);

		// Cheating...
		if ($this->_app['tools']->smcFunc['strlen']($match) >= 3)
			$match = $this->_app['tools']->smcFunc['substr']($match, 0, 3);

		// Let us set a very long-lived cache entry
		if (($return = cache_get_data(Breeze::$name .'-Mentions-'. $match, 7200)) == null)
		{
			$return = array();

			// We need a function in a file far far away...
			require_once($this->_app['tools']->sourceDir . '/Subs-Members.php');

			// Get the members allowed to be mentioned
			$allowedMembers = array_values(membersAllowedTo('breeze_beMentioned'));

			// Huh? no one is allowed? bummer...
			if (empty($allowedMembers))
				return $return;

			// Safety first!
			$allowedMembers = (array) $allowedMembers;

			$result = $this->_app['tools']->smcFunc['db_query']('', '
				SELECT id_member, member_name, real_name
				FROM {db_prefix}members
				WHERE id_member IN({array_int:allowed})
					AND member_name LIKE {string:match} OR real_name LIKE {string:match}',
				array(
					'match' => $match .'%',
					'allowed' => $allowedMembers
				)
			);

			while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($result))
				$return[] = array(
					'name' => $row['real_name'],
					'id' => (int) $row['id_member'],
				);

			$this->_app['tools']->smcFunc['db_free_result']($result);

			// Cached and forget about it
			cache_put_data(Breeze::$name .'-Mentions-'. $match, $return, 7200);
		}

		return $return;
	}

	/**
	 * BreezeQuery::loadMinimalData()
	 *
	 * Quick and dirty way to get an user's name, link and if available, gender.
	 * @param array $user(s) the user ID
	 * @return array containing the following info:  id as key, name, username, link and id.
	 */
	public function loadMinimalData($users)
	{
		global $txt;

		if (empty($users))
			return false;

		// Arrays only please!
		$users = array_unique((array) $users);
		$toLoad = array();
		$returnData = array();
		$toCache = array();

		// Got some stored results?
		foreach ($users as $u)
		{
			$u = (int) $u;

			if (cache_get_data(Breeze::$name .'-'. $u .'-MinimalData', 360))
			{
				$profile = cache_get_data(Breeze::$name .'-'. $u .'-MinimalData', 360);

				$returnData[$u] = array(
					'username' => $profile['member_name'],
					'name' => $profile['real_name'],
					'id' => $profile['id_member'],
					'href' => $this->_app['tools']->scriptUrl . '?action=profile;u=' . $profile['id_member'],
					'link' => '<a href="' . $this->_app['tools']->scriptUrl . '?action=profile;u=' . $profile['id_member'] . '" title="' . $txt['profile_of'] . ' ' . $profile['real_name'] . '">' . $profile['real_name'] . '</a>',
					// 'gender' => $profile['gender'],
				);

				unset($profile);
			}

			// Nope? :(
			else
				$toLoad[] = $u;
		}

		// Well well well...
		if (!empty($toLoad))
		{
			$request = $this->_app['tools']->smcFunc['db_query']('', '
				SELECT id_member, member_name, real_name
				FROM {db_prefix}members
				WHERE id_member IN ({array_int:users})',
				array(
					'users' => $toLoad,
				)
			);

			while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($request))
				$toCache[$row['id_member']] = $row;

			$this->_app['tools']->smcFunc['db_free_result']($request);

			// Yep, another foreach...
			foreach ($toCache as $k => $v)
			{
				cache_put_data(Breeze::$name .'-'. $k .'-MinimalData', $toCache[$k], 360);

				$profile = $toCache[$k];

				// Build the nicely formatted array.
				$returnData[$k] = array(
					'username' => $profile['member_name'],
					'name' => $profile['real_name'],
					'id' => $profile['id_member'],
					'href' => $this->_app['tools']->scriptUrl . '?action=profile;u=' . $profile['id_member'],
					'link' => '<a href="' . $this->_app['tools']->scriptUrl . '?action=profile;u=' . $profile['id_member'] . '" title="' . $txt['profile_of'] . ' ' . $profile['real_name'] . '">' . $profile['real_name'] . '</a>',
				);
			}
		}

		// Any guest accounts?
		foreach ($users as $u)
			if (!isset($returnData[$u]))
				$returnData[$u] = array(
					'username' => $txt['guest_title'],
					'name' => $txt['guest_title'],
					'id' => 0,
					'href' => '',
					'link' => $txt['guest_title'],
					'gender' => 0,
					'guest' => true,
				);

		return $returnData;
	}

	public function wannaSeeBoards()
	{
		global $user_info;

		if (($boards = cache_get_data(Breeze::$name .'-Boards-' . $user_info['id'], 120)) == null)
		{
			$request = $this->_app['tools']->smcFunc['db_query']('', '
				SELECT id_board
				FROM {db_prefix}boards as b
				WHERE {query_wanna_see_board}',
				array()
			);

			while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($request))
				$boards[] = $row['id_board'];

			$this->_app['tools']->smcFunc['db_free_result']($request);
			cache_put_data(Breeze::$name .'-Boards-' . $user_info['id'], $boards, 120);
		}

		return $boards;
	}

	public function userLikes($type, $user = false)
	{
		global $user_info;

		if (empty($user))
			$user = $user_info['id'];

		// Do we already have this?
		if (!empty($this->_userLikes[$user][$type]))
			return $this->_userLikes[$user][$type];

		if (($this->_userLikes[$user][$type] = cache_get_data(Breeze::$name .'-likes-'. $type .'-'. $user, 120)) == null)
		{
			$request = $this->_app['tools']->smcFunc['db_query']('', '
				SELECT content_id
				FROM {db_prefix}user_likes
				WHERE id_member = {int:user}
					AND content_type = {string:type}',
				array(
					'user' => $user,
					'type' => $type,
				)
			);

			// @todo fetch all the columns and not just the content_id, for statistics and stuff...
			while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($request))
				$this->_userLikes[$user][$type][] = (int) $row['content_id'];

			$this->_app['tools']->smcFunc['db_free_result']($request);
			cache_put_data(Breeze::$name .'-likes-'. $type .'-'. $user, $this->_userLikes[$user][$type], 120);
		}

		return $this->_userLikes[$user][$type];
	}

	public function updateLikes($type, $content, $numLikes)
	{
		$this->_app['tools']->smcFunc['db_query']('', '
			UPDATE {db_prefix}' . ($this->_tables[$type]['table']) . '
			SET likes = {int:num_likes}
			WHERE '. ($type) .'_id = {int:id_content}',
			array(
				'id_content' => $content,
				'num_likes' => $numLikes,
			)
		);
	}

	/**
	 * BreezeQuery::getMoodByID()
	 *
	 * Returns the info from 1 or more moods.
	 * @param array|integer $data the user ID, could be an integer or an array of IDs,the function converts it to an array.
	 * @params boolean To indicate if you want to retrieve 1 result only, if false, it will return an associative array with the mood ID as key.
	 * @return array.
	 */
	public function getMoodByID($data, $single = false)
	{
		if (empty($data))
			return false;

		// Work with arrays.
		$data = array_map('intval', (array) $data);
		$moods = array();

		$request = $this->_app['tools']->smcFunc['db_query']('', '
			SELECT '. (implode(', ', $this->_tables['moods']['columns'])) .'
			FROM {db_prefix}' . ($this->_tables['moods']['table']) .'
			WHERE moods_id IN ({array_int:data})',
			array(
				'data' => $data,
			)
		);

		while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($request))
		{
			if($single)
				$moods = $row;

			else
				$moods[$row['moods_id']] = $row;
		}

		$this->_app['tools']->smcFunc['db_free_result']($request);

		return $moods;
	}

	public function getAllMoods()
	{
		if (($moods = cache_get_data(Breeze::$name .'moods-all', 120)) == null)
		{
			$moods = array();
			$request = $this->_app['tools']->smcFunc['db_query']('', '
				SELECT '. (implode(', ', $this->_tables['moods']['columns'])) .'
				FROM {db_prefix}' . ($this->_tables['moods']['table']),
				array()
			);

			while ($row = $this->_app['tools']->smcFunc['db_fetch_assoc']($request))
			{
				$moods[$row['moods_id']] = $row;

				// Save some headaches by adding a nice formatted image url.
				$moods[$row['moods_id']]['image_url'] = $this->_app['mood']->imagesUrl . $row['file'] .'.'. $row['ext'];
				$moods[$row['moods_id']]['image_html'] = '<img src="'. $this->_app['mood']->imagesUrl . $row['file'] .'.'. $row['ext'] .'" alt="'. $row['name'] .'" title="'. $row['description'] .'" class="breeze_mood_image" />';
			}

			$this->_app['tools']->smcFunc['db_free_result']($request);
			cache_put_data(Breeze::$name .'-moods-all', $moods, 120);
		}

		return $moods;
	}

	public function insertMood($data)
	{
		// Just the usual empty check, the data should already be properly handled by someone else.
		if (empty($data))
			return false;

		// Insert!
		$this->_app['tools']->smcFunc['db_insert']('replace', '{db_prefix}' . ($this->_tables['moods']['table']) .
			'', array(
			'name' => 'string',
			'file' => 'string',
			'ext' => 'string',
			'description' => 'string',
			'enable' => 'string'
			), $data, array('moods_id', ));

		// Return the newly created ID.
		return $this->_app['tools']->smcFunc['db_insert_id']('{db_prefix}' . ($this->_tables['moods']['table']), 'moods_id');
	}

	public function updateMood($data)
	{
		if (empty($data))
			return false;

		$this->_app['tools']->smcFunc['db_query']('', '
			UPDATE {db_prefix}' . ($this->_tables['moods']['table']) . '
			SET name = {string:name}, file = {string:file}, ext = {string:ext}, description = {string:description}, enable= {string:enable}
			WHERE moods_id = {int:moods_id}',
			$data
		);
	}

	public function deleteMood($data)
	{
		if (empty($data))
			return false;

		// Work with arrays.
		$data = array_map('intval', (array) $data);

		// Not much to do here, delete the entries and be done with it.
		$this->_app['tools']->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}' . ($this->_tables['moods']['table']) . '
			WHERE moods_id IN ({array_int:data})',
			array(
				'data' => $data,
			)
		);
	}

	protected function generateData($row, $type)
	{
		static $canLike;

		// array equals array O RLY???
		$array = array();

		if (empty($row) || empty($type))
			return $array();

		if (!isset($canLike))
			$canLike = allowedTo('breeze_canLike');

		if ($type == 'status')
		{
			$array = array(
				'id' => $row['status_id'],
				'owner_id' => $row['status_owner_id'],
				'poster_id' => $row['status_poster_id'],
				'time' => $this->_app['tools']->timeElapsed($row['status_time']),
				'time_raw' => $row['status_time'],
				'body' => $this->_app['parser']->display($row['status_body']),
				'comments' => array(),
			);

			if ($this->_app['tools']->setting('likes'))
				$array['likes'] = array(
					'count' => $row['likes'],
					'already' => in_array($row['status_id'], (array) $this->userLikes('breSta')),
					'can_like' => $canLike,
				);
		}

		else if ($type == 'comments')
		{
			$array = array(
				'id' => $row['comments_id'],
				'status_id' => $row['comments_status_id'],
				'status_owner_id' => $row['comments_status_owner_id'],
				'poster_id' => $row['comments_poster_id'],
				'profile_id' => $row['comments_profile_id'],
				'time' => $this->_app['tools']->timeElapsed($row['comments_time']),
				'time_raw' => $row['comments_time'],
				'body' => $this->_app['parser']->display($row['comments_body']),
			);

			if ($this->_app['tools']->setting('likes'))
				$array['likes'] = array(
					'count' => $row['likes'],
					'already' => in_array($row['comments_id'], (array) $this->userLikes('breCom')),
					'can_like' => $canLike,
				);
		}

		return $array;
	}
}

/*
* Saturday is almost over
* getting high on stormy weather
* something to remember
* a sunny day can make you crumble.
*/
