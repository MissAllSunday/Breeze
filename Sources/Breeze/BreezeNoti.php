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
		global $sourcedir;

		$this->_app = $app;

		require_once($sourcedir . '/Subs-Notify.php');
	}

	public function insert($params, $type)
	{
		global $smcFunc;

		if (empty($params) || empty($type))
			return false;

		// Gotta append a type so we can pretend to know what we're doing...
		$params['content_type'] = $type;

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
		if (in_array($this->_details['content_type'], get_class_methods(__CLASS__)))
			$details['content_type']();

		// else fire some error log, dunno...
	}

	protected function checkSpam($user, $action, $sender = false)
	{
		global $smcFunc;

		// No user? no action? no fun...
		if (empty($user) || empty($action))
			return true;

		$request = $smcFunc['db_query']('', '
			SELECT id_alert
			FROM {db_prefix}user_alerts
			WHERE id_member = {int:id_member}
				AND is_read = 0
				AND content_type = {string:content_type}
				AND content_id = {int:content_id}
				AND content_action = {string:content_action}
				'. ($sender ? 'AND id_member_started = {int:sender}' : '') .'',
			array(
				'id_member' => $user,
				'content_type' => $this->_details['content_type'],
				'content_id' => $this->_details['id'],
				'content_action' => $action,
				'sender' => $sender,
			)
		);

		$result = ($smcFunc['db_num_rows']($request) > 0)
		$smcFunc['db_free_result']($request);

		return $result;
	}

	protected function status()
	{
		// Useless to fire you an alert for something you did...
		if ($this->_details['owner_id'] == $this->_details['poster_id'])
			return;

		// Get the preferences for the profile owner
		$prefs = getNotifyPrefs($this->_details['owner_id'], $this->_details['content_type'] . '_owner', true);

		// User does not want to be notified...
		if (empty($prefs[$this->_details['owner_id']][$this->_details['content_type'] . '_owner']))
			return true;

		// Check if the same poster has already posted a status...
		$spam = checkSpam($this->_details['owner_id'], 'like_owner', $this->_details['poster_id']);

		// Theres a status already, just update the time...
		if ($spam)
		{

		}

		// Nope! create the alert!
		else
		{

		}
	}

	protected function comment()
	{

	}

	protected function cover()
	{

	}
}
