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

	protected function innerCreate($params, $checkSpam = true)
	{
		if (empty($params) || !is_array($params))
			return false;

		$spam = false;

		// Get the preferences for the profile owner
		$prefs = getNotifyPrefs($params['id_member'], $params['content_type'], true);

		// User does not want to be notified...
		if (empty($prefs[$params['id_member']][$params['content_type']]))
			return false;

		// Check if the same poster has already fired a notification.
		if ($checkSpam)
			$spam = $this->_app['query']->notiSpam($params['id_member'], $params['content_type'], $params['id_member_started']);

		// Theres a status already, just update the time...
		if ($spam)
			$this->_app['query']->updateAlert(array('alert_time' => $params['alert_time']), $spam);

		// Nope! create the alert!
		else
		{
			$this->_app['query']->createAlert($params);

			// Lastly, update the counter.
			updateMemberData($params['id_member'], array('alerts' => '+'));
		}

		return true;
	}

	protected function status()
	{
		// Useless to fire you an alert for something you did...
		if ($this->_details['owner_id'] == $this->_details['poster_id'])
		return;

		$this->innerCreate(array(
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
	}

	protected function comment()
	{
		// You posted a comment on your own status on your own wall, no need to tell you that. However, fire an alert for your buddies.
		if (($this->_details['poster_id'] == $this->_details['profile_id']) && ($this->_details['profile_id'] == $this->_details['status_owner_id']))
		{
			$this->innerCreate(array(
				'alert_time' => $this->_details['time_raw'],
				'id_member' => $this->_details['status_owner_id'],
				'id_member_started' => $this->_details['poster_id'],
				'member_name' => '',
				'content_type' => $this->_details['content_type'] . '_poster_own_wall',
				'content_id' => $this->_details['id'],
				'content_action' => '',
				'is_read' => 1,
				'extra' => serialize(array(
					'buddy_alert' => true,
					'buddy_text' => 'comment_poster_own_wall'
				)),
			), false);

			// No need to go further.
			return;
		}

		// You posted a comment on somebody else status on your wall? then just notify that "somebody"
		if (($this->_details['poster_id'] == $this->_details['profile_id']) && ($this->_details['poster_id'] != $this->_details['status_owner_id']))
			$this->innerCreate(array(
				'alert_time' => $this->_details['time_raw'],
				'id_member' => $this->_details['status_owner_id'],
				'id_member_started' => $this->_details['poster_id'],
				'member_name' => '',
				'content_type' => $this->_details['content_type'] . '_status_owner',
				'content_id' => $this->_details['id'],
				'content_action' => '',
				'is_read' => 0,
				'extra' => serialize(array(
					'buddy_text' => 'comment_status_owner_buddy',
					'text' => 'comment_status_owner',
				)),
			));

		// You posted a comment on someone's status on someone's wall?
		elseif (($this->_details['poster_id'] != $this->_details['profile_id']) && ($this->_details['poster_id'] != $this->_details['status_owner_id']))
		{
			// Are the profile owner and status owner the same person?
			if ($this->_details['profile_id'] == $this->_details['status_owner_id'])
				$this->innerCreate(array(
					'alert_time' => $this->_details['time_raw'],
					'id_member' => $this->_details['status_owner_id'],
					'id_member_started' => $this->_details['poster_id'],
					'member_name' => '',
					'content_type' => $this->_details['content_type'] . '_status_owner',
					'content_id' => $this->_details['id'],
					'content_action' => '',
					'is_read' => 0,
					'extra' => serialize(array(
						'text' => 'comment_status_owner_own_wall'
						'buddy_text' => 'comment_status_owner_buddy',
					))
				));

			// Nope? then fire 2 alerts.
			else
			{
				// This is for the wall owner.
				$this->innerCreate(array(
					'alert_time' => $this->_details['time_raw'],
					'id_member' => $this->_details['profile_id'],
					'id_member_started' => $this->_details['poster_id'],
					'member_name' => '',
					'content_type' => $this->_details['content_type'] . '_profile_owner',
					'content_id' => $this->_details['id'],
					'content_action' => '',
					'is_read' => 0,
					'extra' => serialize(array(
						'text' => 'comment_different_owner',
						'buddy_text' => 'comment_status_owner_buddy',
					))
				));

				// The status owner gets notified too!
				$this->innerCreate(array(
					'alert_time' => $this->_details['time_raw'],
					'id_member' => $this->_details['profile_id'],
					'id_member_started' => $this->_details['poster_id'],
					'member_name' => '',
					'content_type' => $this->_details['content_type'] . '_status_owner',
					'content_id' => $this->_details['id'],
					'content_action' => '',
					'is_read' => 0,
					'extra' => serialize(array(
						'text' => 'comment_status_owner',
						'buddy_text' => 'comment_status_owner_buddy',
					))
				));
			}
		}
	}

	protected function cover()
	{
		return;
	}
}
