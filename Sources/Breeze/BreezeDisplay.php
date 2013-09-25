<?php

/**
 * BreezeDisplay
 *
 * The purpose of this file is to create proper html based on the type and the info it got.
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013 Jessica González
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

class BreezeDisplay
{
	private $returnArray = array();
	private $params = array();
	private $UserInfo;
	private $tools;
	private $parse;
	private $type;

	function __construct($tools, $text)
	{
		$this->tools = $tools;
		$this->text = $text;
	}

	public function HTML($params, $type, $single = false, $usersToLoad = false)
	{
		global $context;

		if (empty($params) || empty($type))
			return false;

		$return = array();
		$users = array();
		$call = 'breeze_'. $type;

		// Functions template
		loadtemplate(Breeze::$name .'Functions');

		if ($single)
			$params['time'] = $this->tools->timeElapsed($params['time']);

		// Let us work with an array
		$params = $single ? array($params) : $params;

		// If there is something to load, load it then!
		if ($usersToLoad)
			$this->tools->loadUserInfo($usersToLoad);

		// Call the template with return param as true
		$return = $call($params, true);

		// If single is true, return the first (and only) item in the array
		return $return;
	}
}
