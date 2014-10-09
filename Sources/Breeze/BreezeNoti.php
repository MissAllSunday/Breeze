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
