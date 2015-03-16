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
	 * BreezeAjax::__construct()
	 *
	 * Sets all the needed vars, loads the language file
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
	 * Calls the right method for each subaction, calls returnResponse().
	 * @see BreezeAjax::returnResponse()
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
			'deny',
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
		if ((cache_get_data('Buddy-sent-'. $this->_userSender['id'] .'-'. $this->_userReceiver, 86400)) == null)
		{
			// Ran the request through some checks...
			if ($this->denied() == true)
				return;

			// Create a nice alert to let the user know you want to be his/her buddy!
			$this->_app['query']->insertNoti(array(
				'receiver_id' => $this->_userReceiver,
				'id_member' => $this->_userSender['id'],
				'member_name' => $this->_userSender['username'],
				'time' => time(),
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
	}

	/**
	 * BreezeAjax::denied()
	 *
	 * Checks if the receiver does indeed want you as his/her buddy.
	 * @return boolean True if you are blocked, false if you're good boy/girl!
	 */
	protected function denied()
	{
		// Get the receiver's user settings.
		$receiverSettings = $this->_app['query']->getUserSettings($this->_userReceiver);

		// Are you on his/her ignore list?
		if (!empty($receiverSettings['blockIgnoredInvites']) && !empty($receiverSettings['ignoredList']) && in_array($this->_userSender['id'], $receiverSettings['ignoredList']))
			return true;

		// Are you in his/her block list?
		if (!empty($receiverSettings['blockList']) && in_array($this->_userSender['id'], $receiverSettings['blockList']))
			return true;

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
	}

	// When the petitioner wants to add the receiver as friend
	public function confirm()
	{

	}

	// When receiver confirmed the friendship!
	public function confirmed()
	{

	}

	// When the receiver user denies the request DUH!
	public function deny()
	{

	}

	// When you want to block the sender from ever invite you again!
	public function block()
	{

	}

	// Whatever the action performed, show a landing "done" page.
	protected function setResponse()
	{
		global $context;

		// Get the template anf language files.
		loadLanguage(Breeze::$name);
		loadtemplate(Breeze::$name .'Functions');

		// All the generic stuff.
		$context['page_title'] = $this->_app['tools']->text('buddy_title');
		$context['sub_template'] = 'buddy_request';
		$context['linktree'][] = array(
			'url' => $this->_app['tools']->scriptUrl . '?action=buddy'. (!empty($this->_call) ? ';sa='. $this->_call : ''),
			'name' => $context['page_title'],
		);
		$context['response'] = $this->_response;
	}
}
