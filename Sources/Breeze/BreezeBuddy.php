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

		$data = Breeze::data('get');
		$user = $data->get('u');

		// Make sure we got something.
		if (empty($user))
			fatal_lang_error('no_access', false);

		// Figure it out what are we gonna do...

	}

	public function add()
	{

	}

	public function delete()
	{

	}

	public function confirm()
	{

	}

	public function deny()
	{

	}
}
