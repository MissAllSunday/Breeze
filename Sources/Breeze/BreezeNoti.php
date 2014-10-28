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

		// Gotta remove the identifier...
		$call = str_replace(Breeze::$txtpattern, '', $this->_details['content_type']);

		// Call the appropriated method.
		if (in_array($call, get_class_methods(__CLASS__)))
			$this->$call();

		// else fire some error log, dunno...
	}

	protected function innerCreate($params, $checkSpam = true)
	{
		if (empty($params) || !is_array($params))
			return false;

		$spam = false;

		// Get the preferences for the person whos gonna receive this alert.
		$prefs = getNotifyPrefs($params['id_member'], Breeze::$txtpattern . $params['content_type'], true);

		// User does not want to be notified...
		if (empty($prefs[$params['id_member']][Breeze::$txtpattern . $params['content_type']]))
			return false;

		// Check if the same poster has already fired a notification.
		if ($checkSpam)
			$spam = $this->_app['query']->notiSpam($params['id_member'], $params['content_type'], $params['id_member_started']);

		// Before doing anything, serialize the "extra" array.
		if (!empty($params['extra']))
			$params['extra'] = serialize($params['extra']);

		// Theres an alert already, just update the time...
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

	protected function like()
	{
		$row = $this->_details['like_type'] .'_id';
		$authorColumn = $this->_details['like_type'] .'_poster_id';

		// With the given values, try to find who is the owner of the liked content.
		$data = $this->_app['query']->getSingleValue($this->_details['like_type'], $row, $content);

		// So, whos gonna receive this alert?
		$messageOwner = !empty($data) ? $data['poster_id'] : 0;

		// Two types of alerts, either a comment or a status.
		$this->innerCreate(array(
			'alert_time' => $this->_details['time'],
			'id_member' => $messageOwner,
			'id_member_started' => $this->_details['user'],
			'member_name' => '',
			'content_type' => $this->_details['content_type'],
			'content_id' => $this->_details['content'],
			'content_action' => '',
			'is_read' => 0,
			'extra' => array(
				'text' => 'like_'. $this->_details['like_type'],
				'buddy_text' => 'like_'. $this->_details['like_type'] .'_buddy',
				'toLoad' => array($messageOwner, $this->_details['user']),
				'status_id' => $this->_details['like_type'] == 'status' ? $this->_details['content'] : $data['status_id'],
				'comment_id' => $this->_details['like_type'] == 'comments' ? $this->_details['content'] : 0,
				'wall_owner' => $data[$this->_details['like_type'] == 'comments' ? 'profile_id' : 'owner_id'],
			),
		));
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
				'extra' => array(
					'buddy_alert' => true,
					'buddy_text' => 'comment_poster_own_wall',
					'toLoad' => array($this->_details['status_owner_id'], $this->_details['poster_id'], $this->_details['profile_id']),
					'wall_owner' => $this->_details['profile_id'],
					'poster' => $this->_details['poster_id'],
					'status_owner' => $this->_details['status_owner_id'],
					'status_id' => $this->_details['status_id'],
				),
			), false);

			// No need to go further.
			return;
		}

		// Set a basic array. Despise all the different alternatives this notification has, only a few things actually change...
		$toCreate = array(
			'alert_time' => $this->_details['time_raw'],
			'id_member' => $this->_details['status_owner_id'],
			'id_member_started' => $this->_details['poster_id'],
			'member_name' => '',
			'content_type' => $this->_details['content_type'] . '_status_owner',
			'content_id' => $this->_details['id'],
			'content_action' => '',
			'is_read' => 0,
			'extra' => array(
					'toLoad' => array($this->_details['status_owner_id'], $this->_details['poster_id'], $this->_details['status_owner_id']),
					'wall_owner' => $this->_details['profile_id'],
					'poster' => $this->_details['poster_id'],
					'status_owner' => $this->_details['status_owner_id'],
					'status_id' => $this->_details['status_id'],
			),
		);

		// You posted a comment on somebody else status on your wall? then just notify that "somebody"
		if (($this->_details['poster_id'] == $this->_details['profile_id']) && ($this->_details['poster_id'] != $this->_details['status_owner_id']))
		{
			$toCreate['extra']['buddy_text'] = 'comment_status_owner_buddy';
			$toCreate['extra']['text'] = 'comment_status_owner';
		}

		// You posted a comment on someone's status on someone's wall?
		elseif (($this->_details['poster_id'] != $this->_details['profile_id']) && ($this->_details['poster_id'] != $this->_details['status_owner_id']))
		{
			// Are the profile owner and status owner the same person?
			if ($this->_details['profile_id'] == $this->_details['status_owner_id'])
			{
				$toCreate['extra']['buddy_text'] = 'comment_status_owner_buddy';
				$toCreate['extra']['text'] = 'comment_status_owner_own_wall';
			}

			// Nope? then fire 2 alerts.
			else
			{
				// This is for the wall owner. A completely separate alert actually...
				$this->innerCreate(array(
					'alert_time' => $this->_details['time_raw'],
					'id_member' => $this->_details['profile_id'],
					'id_member_started' => $this->_details['poster_id'],
					'member_name' => '',
					'content_type' => $this->_details['content_type'] . '_profile_owner',
					'content_id' => $this->_details['id'],
					'content_action' => '',
					'is_read' => 0,
					'extra' => array(
						'text' => 'comment_different_owner_own_wall',
						'buddy_text' => 'comment_status_owner_buddy',
						'toLoad' => array($this->_details['status_owner_id'], $this->_details['poster_id'], $this->_details['status_owner_id']),
						'wall_owner' => $this->_details['profile_id'],
						'poster' => $this->_details['poster_id'],
						'status_owner' => $this->_details['status_owner_id'],
						'status_id' => $this->_details['status_id'],
					),
				));

				// The status owner gets notified too!
				$toCreate['extra']['buddy_text'] = 'comment_status_owner_buddy';
				$toCreate['extra']['text'] = 'comment_status_owner';
			}
		}

		// Create the alert already!
		$this->innerCreate($toCreate);
	}

	protected function cover()
	{
		return;
	}
}
