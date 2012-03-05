<?php

/**
 * Breeze_Notifications
 *
 * The purpose of this file is to fetch all notifications for X user
 * @package Breeze mod
 * @version 1.0 Beta 1
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2012, Jessica González
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
	die('Hacking attempt...');

class Breeze_Notifications
{
	$types = array();
	$params = array();
	$user = 0;
	$settings = '';
	$query = '';

	function __construct()
	{
		$this->types = array(
			'comment',
			'status',
			'like',
			'buddy'
		);

		Breeze::Load(array(
			'Settings',
			'Query',
		));

		/* We kinda need all this stuff, dont' ask why, just nod your head... */
		$this->settings = Breeze_Settings::getInstance();
		$this->query = Breeze_Query::getInstance();
	}

	public function Create($type, $params)
	{
		if (in_array($type, $this->types) && !empty($params))
		{
			$this->params = $params;

			switch ($type)
			{
				case 'comment':
					$this->NewComment();
					break;
				case 'status':
					$this->NewStatus();
					break;
				case 'like':
					$this->NewLike();
				case 'buddy':
					$this->NewBuddy();
					break;
			}
		}
	}

	protected function NewComment()
	{

	}

	protected function NewStatus()
	{

	}

	protected function NewLike()
	{

	}

	protected function NewBuddy()
	{

	}

	public function GetByUser($user)
	{
		/* Dont even bother... */
		if (empty($user))
			return;

		$this->user = $user;

	}

	public function Stream()
	{

	}

	protected function Delete()
	{


	}
}