<?php

/**
 * BreezeNoti
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeNoti
{
	protected $_app;
	protected $_details;

	public function __construct($app)
	{
		$this->_app = $app;
	}

	public function insert($params, $type)
	{
		global $smcFunc;

		if (empty($params) || empty($type))
			return false;

		// Gotta append a type so we can pretend to know what we're doing...
		$params['bType'] = $type;

		$smcFunc['db_insert']('insert',
			'{db_prefix}background_tasks',
			array('task_file' => 'string', 'task_class' => 'string', 'task_data' => 'string', 'claimed_time' => 'int'),
			array('$sourcedir/tasks/Breeze-Notify.php', 'Breeze_Notify_Background', serialize($params), 0),
			array('id_task')
		);
	}

	public function call($details)
	{
		if (empty($details) || !is_array($details))
			return false;

		$this->_details = $details;

		// Call the appropriated method.
		if (in_array($this->_details['bType'], get_class_methods(__CLASS__)))
			$details['bType']();

		// else fir some error log, dunno...
	}

	protected function status()
	{

	}

	protected function comment()
	{

	}

	protected function cover()
	{

	}
}
