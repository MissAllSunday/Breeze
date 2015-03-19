<?php

/**
 * BreezeAjax
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeBuddy
{
	protected $_app;
	protected $_userReceiver = 0;
	protected $_userSender = 0;
	protected $_senderConfirm = 0;
	protected $_receiverConfirm = 0;
	protected $_call = '';
	protected $_data = false;

	/**
	 * BreezeBuddy::__construct()
	 *
	 * Sets all the needed vars, loads the language file.
	 * @return void
	 */
	public function __construct($app)
	{
		$this->_app = $app;

		// Needed to show some strings.
		loadLanguage(Breeze::$name);
	}

	/**
	 * BreezeAjax::call()
	 *
	 * Calls the right method for each subaction, calls setResponse().
	 * @see BreezeBuddy::setResponse()
	 * @return void
	 */
	public function call()
	{
		global $user_info;

		checkSession('get');
		is_not_guest();

		$this->_data = Breeze::data('request');
		$this->_call = $this->_data->get('sa');
		$subActions = array(
			'confirm',
			'confirmed',
			'decline',
			'block',
		);

		// Figure it out what are we gonna do... check the subactions first!
		if ($this->_call && in_array($this->_call, $subActions))
		{
			// We need a "sender" ID.
			$this->_senderConfirm = $this->_data->get('sender');
			$this->_receiverConfirm = $user_info;

			if (!$this->_senderConfirm)
				fatal_lang_error('no_access', false);

			$this->{$this->_data->get('sa')}();
		}

		// An standard add/delete call.
		else
		{
			// To avoid confusions, set these properties here.
			$this->_userReceiver = $this->_data->get('u');
			$this->_userSender = $user_info;

			// Make sure we got something.
			if (!$this->_userReceiver)
				fatal_lang_error('no_access', false);

			// Remove if it's already there...
			if (in_array($this->_userReceiver, $this->_userSender['buddies']))
				$this->delete();

			// ...or add if it's not and if it's not you.
			elseif ($this->_userSender != $this->_userReceiver)
				$this->add();
		}

		// Anyway, show a nice landing page.
		$this->setResponse();
	}

	public function add()
	{
		global $context;

		// Don't do this that often..
		if (cache_get_data('Buddy-sent-'. $this->_userSender['id'] .'-'. $this->_userReceiver, 86400) == null)
		{
			// Ran the request through some checks...
			if ($this->check() == true)
				return;

			// Create a nice alert to let the user know you want to be his/her buddy!
			$this->_app['query']->insertNoti(array(
				'receiver_id' => $this->_userReceiver,
				'id_member' => $this->_userSender['id'],
				'member_name' => $this->_userSender['username'],
				'time' => time(),
				'text' => 'confirm',
				'sender' => $this->_userSender['id'],
				'receiver' => $this->_userReceiver,
			), 'buddyConfirm');

			// Get the receiver's link
			$this->_app['tools']->loadUserInfo($this->_userReceiver);

			// I actually need to use $context['Breeze']['user_info'] a lot more...
			$this->_response = $this->_app['tools']->parser($this->_app['tools']->text('buddy_confirm'), array(
				'receiver' => $context['Breeze']['user_info'][$this->_userReceiver]['link'],
			));

			// Store this in a cache entry to avoid creating multiple alerts. Give it a long life cycle.
			cache_put_data('Buddy-sent-'. $this->_userSender['id'] .'-'. $this->_userReceiver, '1', 86400);
		}

		// Let this user know that an alert has already been sent...
		else
			$this->_response = $this->_app['tools']->parser($this->_app['tools']->text('already_sent'), array(
			'receiver' => $context['Breeze']['user_info'][$this->_userReceiver]['link'],
		));
	}

	/**
	 * BreezeAjax::denied()
	 *
	 * Checks if the receiver does indeed want you as his/her buddy.
	 * @return boolean True if you are blocked, false if you're good boy/girl!
	 */
	protected function check()
	{
		// Get the receiver's user settings.
		$receiverSettings = $this->_app['query']->getUserSettings($this->_userReceiver);

		// Are you on his/her ignore/block list?
		if ((!empty($receiverSettings['ignoredList']) && in_array($this->_userSender['id'], $receiverSettings['ignoredList'])) || (!empty($receiverSettings['blockList']) && in_array($this->_userSender['id'], $receiverSettings['blockList'])))
		{
			$this->_response = $this->_app['tools']->text('buddy_blocked');
			return true;
		}

		// And you passed the test!
		return false;
	}

	public function delete()
	{
		global $user_info;

		// Easy, just delete the entry and be done with it.
		$user_info['buddies'] = array_diff($user_info['buddies'], array($this->_userReceiver));

		// Update the settings.
		updateMemberData($user_info['id'], array('buddy_list' => implode(',', $user_info['buddies'])));

		// Now, get the receiver's buddy list and delete the sender from it.
		$receiverSettings = $this->_app['query']->getUserSettings($this->_userReceiver);

		if (!empty($receiverSettings['buddiesList']))
		{
			$receiverSettings['buddiesList'] = array_diff($receiverSettings['buddiesList'], array($this->_userSender['id']));
			updateMemberData($this->_userReceiver, array('buddy_list' => implode(',', $receiverSettings['buddiesList'])));
		}

		// @todo set $this->_response
	}

	// When the petitioner wants to add the receiver as friend
	public function confirm()
	{
		global $context;

		// Load the icon's css.
		loadCSSFile('//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', array('external' => true));

		// Load the sender's data.
		$this->_app['tools']->loadUserInfo($this->_senderConfirm);

		// Prepare the options.
		$confirm = $this->_app['tools']->scriptUrl . '?action=buddy;sa=confirmed;sender=' . $this->_senderConfirm;
		$decline = $this->_app['tools']->scriptUrl . '?action=buddy;sa=decline;sender=' . $this->_senderConfirm;

		$this->_response = $this->_app['tools']->parser($this->_app['tools']->text('buddy_chose'), array(
			'href_confirm' => $confirm,
			'href_decline' => $decline,
			'sender' => $context['Breeze']['user_info'][$this->_senderConfirm]['link'],
		));
	}

	// When receiver confirmed the friendship!
	public function confirmed()
	{
		global $user_info;

		// Royal Entrance Fanfare please!
		$senderSettings = $this->_app['query']->getUserSettings($this->_senderConfirm);
		$receiverSettings = $this->_app['query']->getUserSettings($this->_receiverConfirm['id']);

		// Gotta use $user_info instead of $this->_receiverConfirm because we want this change to take effect immediately!
		if (empty($user_info['buddies']) || (!empty($user_info['buddies']) && !in_array($this->_senderConfirm, $user_info['buddies'])))
		{
			$user_info['buddies'][] = $this->_senderConfirm;
			updateMemberData($user_info['id'], array('buddy_list' => implode(',', $user_info['buddies'])));
		}

		// Now update the sender's buddy list. Gotta check if the receiver isn't already there BUT we need to be careful since the sender's buddy list can be empty!
		if (empty($senderSettings['buddyList']) || (!empty($senderSettings['buddyList']) && !in_array($this->_receiverConfirm['id'], $senderSettings['buddyList'])))
		{
			$senderSettings['buddyList'][] = $this->_receiverConfirm['id'];
			updateMemberData($this->_senderConfirm, array('buddy_list' => implode(',', $senderSettings['buddyList'])));
		}

		// Let the sender know the receiver gladly accepted the invitation.
		$this->_app['query']->insertNoti(array(
			'receiver_id' => $this->_senderConfirm,
			'id_member' => $this->_receiverConfirm['id'],
			'member_name' => $this->_receiverConfirm['username'],
			'time' => time(),
			'text' => 'confirmed',
			'sender' => $this->_senderConfirm,
			'receiver' => $this->_receiverConfirm['id'],
		), 'buddyConfirm');

		// Does the sender wants the world to take note of this great achievement?
		if (!empty($senderSettings['alert_buddyConfirmation']))
			$this->_app['query']->createLog(array(
				'member' => $this->_senderConfirm,
				'content_type' => 'buddy_confirmed',
				'content_id' => 0,
				'time' => time(),
				'extra' => array(
					'sender' => $this->_senderConfirm,
					'receiver' => $this->_receiverConfirm['id'],
					'toLoad' => array($this->_receiverConfirm['id'], $this->_senderConfirm),
				),
			));

		// How about the receiver?
		if (!empty($receiverSettings['alert_buddyConfirmation']))
			$this->_app['query']->createLog(array(
				'member' => $this->_receiverConfirm,
				'content_type' => 'buddy_confirmed',
				'content_id' => 0,
				'time' => time(),
				'extra' => array(
					'sender' => $this->_senderConfirm,
					'receiver' => $this->_receiverConfirm['id'],
					'toLoad' => array($this->_receiverConfirm['id'], $this->_senderConfirm),
				),
			));

		// Lastly, set a nice confirmed message.
		$this->_response = $this->_app['tools']->text('buddy_confirmed_done');
	}

	// When the receiver user denies the request DUH!
	public function decline()
	{
		// Offer an option to block this person
		$this->_response = $this->_app['tools']->parser($this->_app['tools']->text('buddy_decline'), array(
			'href' => $this->_app['tools']->scriptUrl . '?action=buddy;sa=block;sender=' . $this->_senderConfirm,
		));
	}

	// When you want to block the sender from ever invite you again!
	public function block()
	{
		// Get the current user settings.
		$currentSettings = $this->_app['query']->getUserSettings($this->_receiverConfirm['id']);

		// Add this person to the user's "block" list.
		$blockList = !empty($currentSettings['blockList']) ? $currentSettings['blockList'] : array();

		// There might be the possibility that you already are blocking this user...
		if (in_array($this->_senderConfirm, $blockList))
			return $this->_response = $this->_app['tools']->text('buddy_already_blocked');

		// Add the user.
		$blockList[] = $this->_senderConfirm;

		$this->_app['query']->insertUserSettings(array('blockList' => implode(',', $blockList)), $this->_receiverConfirm['id']);

		$this->_response = $this->_app['tools']->text('buddy_blocked_done');
	}

	// Whatever the action performed, show a landing "done" page.
	protected function setResponse()
	{
		global $context;

		// Get the template and language files.
		loadLanguage(Breeze::$name);
		loadtemplate(Breeze::$name .'Functions');

		// All the generic stuff.
		$context['page_title'] = $this->_app['tools']->text('buddy_title');
		$context['sub_template'] = 'buddy_request';
		$context['linktree'][] = array(
			'url' => $this->_app['tools']->scriptUrl . '?action=buddy'. (!empty($this->_call) ? ';sa='. $this->_call : '') . (!empty($this->_senderConfirm) ? ';sender='. $this->_senderConfirm : '') . (!empty($this->_userReceiver) ? ';u='. $this->_senderConfirm : '') .';'. $context['session_var'] .'='. $context['session_id'],
			'name' => $context['page_title'],
		);

		$context['response'] = !empty($this->_response) ? $this->_response : '';
	}
}
