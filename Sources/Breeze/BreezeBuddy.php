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

		isAllowedTo('profile_identity_own');
		is_not_guest();

		$this->_data = Breeze::data('request');
		$this->_userReceiver = $this->_data->get('u');
		$this->_call = $this->_data->get('sa');
		$subActions = array(
			'confirm',
			'deny',
			'denied',
		);

		// Make sure we got something.
		if (empty($user))
			fatal_lang_error('no_access', false);

		// Figure it out what are we gonna do... check the subactions first!
		if ($this->_call && in_array($this->_call, $subActions))
			$this->{$this->_data->get('sa')}();

		// An standard add/delete call.
		else
		{
			// Remove if it's already there...
			if (in_array($this->_userReceiver, $user_info['buddies']))
				$this->delete();

			// ...or add if it's not and if it's not you.
			elseif ($user_info['id'] != $this->_userReceiver)
				$this->confirm();
		}

		// Anyway, show a nice landing page.
		$this->done();
	}

	public function add()
	{

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

	// When the receiver user denies the request DUH!
	public function deny()
	{

	}

	// When the receiver user denied the petitioner and the petitioner got added to the receiver "block list".
	public function denied()
	{

	}

	// Whatever the action performed, show a landing "done" page.
	protected function done()
	{

	}
}
