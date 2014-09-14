<?php

/**
 * BreezeNotifications
 *
 * The purpose of this file is to fetch all notifications for X user
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeNotifications
{
	protected $_app;

	/**
	 * BreezeNotifications::__construct()
	 *
	 * @return
	 */
	function __construct($app)
	{

		// We kinda need all this stuff, don't' ask why, just nod your head...
		$this->_app = $app;
	}

	public function insertTask($data)
	{
		global $smcFunc;

		$smcFunc['db_insert']('insert',
			'{db_prefix}background_tasks',
			array('task_file' => 'string', 'task_class' => 'string', 'task_data' => 'string', 'claimed_time' => 'int'),
			array('$sourcedir/tasks/Breeze-Notify.php', 'call', serialize($data), 0),
			array('id_task')
		);
	}
}
