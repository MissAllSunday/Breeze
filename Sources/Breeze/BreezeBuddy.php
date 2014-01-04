<?php

/**
 * BreezeBuddy
 *
 * The purpose of this file is to replace the default buddy action in SMF with one that provides more functionality.
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://missallsunday.com code.
 *
 * The Initial Developer of the Original Code is
 * Jessica González.
 * Portions created by the Initial Developer are Copyright (c) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeBuddy
{
	public function  __construct($tools, $query, $notifications)
	{
		$this->notification = $notifications;
		$this->query = $query;
		$this->tools = $tools;
	}

	public function buddy()
	{
		global $user_info, $scripturl, $context;

		checkSession('get');

		isAllowedTo('profile_identity_own');
		is_not_guest();

		// We need all this stuff
		$sa = Breeze::data('get');

		/* @todo theres a lot of ifs here... better split all cases into small methods */
		$subActions = array(
			'wait',
			'force',
			'invite',
			'remove',
			'removeBoth',
		);

		// There's gotta be an user...
		if ($sa->validate('u') == false)
			fatal_lang_error('no_access', false);


	}

	public function showBuddyRequests($user)
	{
		// I don't have time for this...
		if (empty($user))
			return false;

		// Load all buddy request for this user
		return $this->query->getNotificationByType('buddy', $user);
	}
}
