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

		require_once($this->_app['tools']->sourceDir . '/Subs-Notify.php');
	}

	public function call($details)
	{
		if (empty($details) || !is_array($details))
			return false;

		$this->_details = $details;

		// Call the appropriated method.
		if (in_array($this->_details['content_type'], get_class_methods(__CLASS__)))
			$this->$details['content_type']();

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
			return;

		// Check if the same poster has already posted a status...
		$spam = $this->_app['query']->notiSpam($this->_details['owner_id'], 'status_owner', $this->_details['poster_id']);

		// Theres a status already, just update the time...
		if ($spam)
			$this->_app['query']->updateAlert(array('alert_time' => $this->_details['time_raw']), $spam);

		// Nope! create the alert!
		else
		{
			$this->_app['query']->createAlert(array(
				'alert_time' => $this->_details['time_raw'],
				'id_member' => $this->_details['owner_id'],
				'id_member_started' => $this->_details['poster_id'],
				'member_name' => '',
				'content_type' => $this->_details['content_type'] . '_owner',
				'content_id' => $this->_details['id'],
				'content_action' => '',
				'is_read' => 0,
				'extra' => ''
			));

			// Lastly, update the counter.
			updateMemberData($this->_details['owner_id'], array('alerts' => '+'));
		}
	}

	protected function comment()
	{
		// You posted a comment on your own wall, no need to tell you that.
		if ($this->_details['poster_id'] == $this->_details['profile_id'])
			return;

		$samePerson = false;
		$alreadySent = false;

		// Before getting all hyped up, lets see if the poster hasn't commented already.
		$spam = $this->_app['query']->notiSpam($this->_details['poster_id'], 'like_owner', $this->_details['poster_id']);

		// OK, there's actually two people we need to alert and two different alert preferences, the profile owner and the status owner.
		// I could probably make this even messier by adding a "notify me on replies to my status made on my own wall" or "notify me on replies to my status on any wall..."
		$prefStatus = getNotifyPrefs($this->_details['status_owner_id'], $this->_details['content_type'] . '_status_owner', true);
		$prefProfile = getNotifyPrefs($this->_details['profile_id'], $this->_details['content_type'] . '_profile_owner', true);

		// What if the status owner and profile owner are the same person? well, in that case go to the profile owner preferences. Of course this means the user has to have the profile owner pref enable...
		if ($this->_details['profile_id'] == $this->_details['status_owner_id'])
			$samePerson = true;

		// Does the status poster wants to be notified?
		if (!empty($prefs[$this->_details['profile_id']][$this->_details['content_type'] . '_profile_owner']))
		{
			// Spam check.

			// Update the alert.


			// Create the alert.

			// If this is the same person, tell everyone the alert has been sent.
			if ($samePerson)
				$alreadySent = true;

			// Lastly, update the counter.
			updateMemberData($this->_details['profile_id'], array('alerts' => '+'));
		}

		// Does the profile owner wants to be notified?
	}

	protected function cover()
	{
		return;
	}
}
