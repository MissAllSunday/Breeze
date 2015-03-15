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
		if (method_exists($this, $call))
			$this->{$call}();

		// else fire some error log, dunno...
	}

	protected function innerCreate($params, $checkSpam = true)
	{
		if (empty($params) || !is_array($params))
			return false;

		$spam = false;

		// Get the preferences for the person whos gonna receive this alert.
		$prefs = getNotifyPrefs($params['id_member'], $params['content_type'], true);

		// User does not want to be notified...
		if (empty($prefs[$params['id_member']][$params['content_type']]))
			return false;

		// Check if the same poster has already fired a notification.
		if ($checkSpam)
			$spam = $this->_app['query']->notiSpam($params['id_member'], $params['content_type'], $params['id_member_started']);

		// Before doing anything, serialize the "extra" array.
		if (!empty($params['extra']))
			$params['extra'] = serialize($params['extra']);

		// There's an alert already, just update the time...
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
		// Don't do anything if this is an "unlike" action.
		if ($this->_details['alreadyLiked'])
			return;

		// Prepare some stuff.
		$row = $this->_details['like_type'] .'_id';
		$authorColumn = $this->_details['like_type'] .'_poster_id';

		// With the given values, try to find who is the owner of the liked content.
		$data = $this->_app['query']->getSingleValue($this->_details['like_type'], $row, $this->_details['content'], true);

		// So, whos gonna receive this alert?
		$messageOwner = !empty($data) ? $data[$this->_details['like_type'] .'_poster_id'] : 0;

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
				'text' => 'like',
				'comment_owner' => $messageOwner,
				'toLoad' => array($messageOwner, $this->_details['user']),
				'status_id' => $data[($this->_details['like_type'] == 'comments' ? $this->_details['like_type'] .'_' : '') . 'status_id'],
				'comment_id' => $this->_details['like_type'] == 'comments' ? $this->_details['content'] : 0,
				'wall_owner' => $data[$this->_details['like_type'] == 'comments' ? 'comments_profile_id' : 'status_owner_id'],
				'like_type' => $this->_details['like_type'],
			),
		));

		// Don't forget the inner alert.
		$uSettings = $this->_app['query']->getUserSettings($this->_details['user']['id']);

		if (!empty($uSettings['alert_like']))
			$this->_app['query']->createLog(array(
				'member' => $this->_details['user']['id'],
				'content_type' => 'like',
				'content_id' => $this->_details['content'],
				'time' => $this->_details['time'],
				'extra' => array(
					'text' => 'like_'. $this->_details['like_type'],
					'buddy_text' => 'like_'. $this->_details['like_type'] .'_buddy',
					'comment_owner' => $messageOwner,
					'toLoad' => array($messageOwner, $this->_details['user']['id']),
					'status_id' => $data[($this->_details['like_type'] == 'comments' ? $this->_details['like_type'] .'_' : '') . 'status_id'],
					'comment_id' => $this->_details['like_type'] == 'comments' ? $this->_details['content'] : 0,
					'wall_owner' => $data[$this->_details['like_type'] == 'comments' ? 'comments_profile_id' : 'status_owner_id'],
					'like_type' => $this->_details['like_type'],
				),
			));
	}

	protected function status()
	{
		// Useless to fire you an alert for something you did.
		if ($this->_details['profile_id'] != $this->_details['poster_id'])
			$this->innerCreate(array(
				'alert_time' => $this->_details['time_raw'],
				'id_member' => $this->_details['profile_id'],
				'id_member_started' => $this->_details['poster_id'],
				'member_name' => '',
				'content_type' => $this->_details['content_type'] . '_owner',
				'content_id' => $this->_details['id'],
				'content_action' => '',
				'is_read' => 0,
				'extra' => array(
					'text' => 'alert_status_owner',
					'toLoad' => array($this->_details['poster_id'], $this->_details['profile_id']),
					'poster' => $this->_details['poster_id'],
					'owner' => $this->_details['profile_id'],
				),
			));

		// And our very own alert too.
		$uSettings = $this->_app['query']->getUserSettings($this->_details['poster_id']);

		if (!empty($uSettings['alert_status']))
			$this->_app['query']->createLog(array(
				'member' => $this->_details['poster_id'],
				'content_type' => 'status',
				'content_id' => $this->_details['id'],
				'time' => $this->_details['time_raw'],
				'extra' => array(
					'buddy_text' => 'alert_status_owner_buddy',
					'toLoad' => array($this->_details['poster_id'], $this->_details['profile_id']),
					'wall_owner' => $this->_details['profile_id'],
					'poster' => $this->_details['poster_id'],
					'status_id' => $this->_details['id'],
				),
			));
	}

	protected function comment()
	{
		// You posted a comment on your own status on your own wall, no need to tell you that. However, fire an alert for your buddies.
		$uSettings = $this->_app['query']->getUserSettings($this->_details['poster_id']);

		if (!empty($uSettings['alert_comment']))
			$this->_app['query']->createLog(array(
				'member' => $this->_details['poster_id'],
				'content_type' => 'comment',
				'content_id' => $this->_details['id'],
				'time' => $this->_details['time_raw'],
				'extra' => array(
					'buddy_text' => 'alert_comment_status_owner_buddy',
					'toLoad' => array($this->_details['status_owner_id'], $this->_details['poster_id'], $this->_details['status_owner_id']),
					'wall_owner' => $this->_details['profile_id'],
					'poster' => $this->_details['poster_id'],
					'status_owner' => $this->_details['status_owner_id'],
					'status_id' => $this->_details['status_id'],
				),
			));

		// No need to go further.
		if (($this->_details['poster_id'] == $this->_details['profile_id']) && ($this->_details['profile_id'] == $this->_details['status_owner_id']))
			return;

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
					'buddy_text' => 'alert_comment_status_owner_buddy',
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
			$toCreate['extra']['buddy_text'] = 'alert_comment_status_owner_buddy';
			$toCreate['extra']['text'] = 'comment_status_owner';
		}

		// You posted a comment on someone's status on someone's wall?
		elseif (($this->_details['poster_id'] != $this->_details['profile_id']) && ($this->_details['poster_id'] != $this->_details['status_owner_id']))
		{
			// Are the profile owner and status owner the same person?
			if ($this->_details['profile_id'] == $this->_details['status_owner_id'])
			{
				$toCreate['extra']['buddy_text'] = 'alert_comment_status_owner_buddy';
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
						'buddy_text' => 'alert_comment_status_owner_buddy',
						'toLoad' => array($this->_details['status_owner_id'], $this->_details['poster_id'], $this->_details['status_owner_id']),
						'wall_owner' => $this->_details['profile_id'],
						'poster' => $this->_details['poster_id'],
						'status_owner' => $this->_details['status_owner_id'],
						'status_id' => $this->_details['status_id'],
					),
				));

				// The status owner gets notified too!
				$toCreate['extra']['buddy_text'] = 'alert_comment_status_owner_buddy';
				$toCreate['extra']['text'] = 'comment_status_owner';
			}
		}

		// Create the alert already!
		$this->innerCreate($toCreate);
	}

	protected function mention()
	{
		global $language;

		require_once($this->_app['tools']->sourceDir . '/Subs-Post.php');
		require_once($this->_app['tools']->sourceDir . '/Mentions.php');

		// Insert the mention.
		Mentions::insertMentions(Breeze::$txtpattern . $this->_details['innerType'], $this->_details['id'], $this->_details['users'], $this->_details['poster_id']);

		// Get the preferences of those who were mentioned.
		$prefs = getNotifyPrefs(array_keys($this->_details['users']), Breeze::$txtpattern . $this->_details['content_type'], true);

		$mentionedMembers = Mentions::getMentionsByContent(Breeze::$txtpattern . $this->_details['innerType'], $this->_details['id'], array_keys($this->_details['users']));

		if (!empty($mentionedMembers))
			foreach ($mentionedMembers as $id => $member)
			{
				// Does this user wants to be notified?
				if (empty($prefs[$id][Breeze::$txtpattern . $this->_details['content_type']]))
					continue;

				$url = '?action=wall;sa=single;u=' . $this->_details['profile_id'] .';bid='. (!empty($this->_details['status_id']) ? ($this->_details['status_id'] .';cid='. $this->_details['id']) : $this->_details['id']);
				$text = '';
				$toload = array($member['mentioned_by']['id'], $member['id'], $this->_details['poster_id']);

				// Add the status poster to the array of to load IDs.
				if ($this->_details['innerType'] == 'com')
					$toLoad[] = $this->_details['status_owner_id'];

				// Is it your own wall? Ternary abuse time!
				$text = 'mention_'. ($member['id'] == $this->_details['profile_id'] ? 'own_' : '') . ($this->_details['innerType'] == 'sta' ? 'status' : 'comment');

				$this->_app['query']->createAlert(array(
					'alert_time' => time(),
					'id_member' => $member['id'],
					'id_member_started' => $member['mentioned_by']['id'],
					'member_name' => $member['mentioned_by']['name'],
					'content_type' => 'mention',
					'content_id' => $this->_details['id'],
					'content_action' => 'mention',
					'is_read' => 0,
					'extra' => serialize(array(
						'text' => $text,
						'url' => $url,
						'toLoad' => $toload,
						'profile_owner' => $this->_details['profile_id'],
					)),
				));

				// Lastly, update the counter.
				updateMemberData($member['id'], array('alerts' => '+'));
			}
	}

	protected function buddyConfirm()
	{
		// Do not check for preferences, just send the alert straight away!
		$this->_app['query']->createAlert(array(
			'alert_time' => $this->_details['time'],
			'id_member' => $this->_details['receiver_id'],
			'id_member_started' => $this->_details['id_member'],
			'member_name' => $this->_details['member_name'],
			'content_type' => 'buddy_request',
			'content_action' => '',
			'is_read' => 0,
			'extra' => '',
		));

	}
}
