<?php

/**
 * Breeze_Query
 *
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011, Jessica González
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
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Breeze_Query
{
	private static $instance;
	private $r = array();
	private $query = array();
	private $data = array();
	private $query_params = array(
				'rows' =>'*',
			);
	private $temp = array();
	private $temp2 = array();
	private $valid = false;

	private function __construct()
	{
		global $context;

		Breeze::LoadMethod('DB');

		$this->query = $context['Breeze'];
		$this->data = array(
			'status' => new Breeze_DB('breeze_status'),
			'comments' => new Breeze_DB('breeze_comments'),
			'user_settings' => new Breeze_DB('breeze_user_settings'),
			'user_settings_modules' => new Breeze_DB('breeze_user_settings_modules'),
			'visit_log' => new Breeze_DB('breeze_visit_log')
		)
	}

	public static function getInstance()
	{
		if (!self::$instance)
		{
			self::$instance = new Breeze_Query();
		}

		return self::$instance;
	}

	private function AddToContext($name, $array)
	{
		if (empty($name) || empty($array))
			return;

		global $context;

		$context['Breeze'][$name] = $array;
	}

	private function GetReturn($table, $row, $value)
	{
		/* Get the raw data */
		$this->temp = $this->$table();

		/* Safety first */
		$value = (int) $value;

		/* Do this only if there is something to work with */
		if ($this->valid)
		{
			/* Generate an array with a defined key */
			foreach($this->temp as $t)
				$this->temp2[$t[$row]] = $t;

			/* If the key isn't equal to what we are looking for then unset it and create a new array with the info we want */
			foreach($this->temp2 as $t2)
			{
				if ($t2 != $value)
					unset($t2)

				else
					$this->r = $t2;
			}
		}

		/* Return the info we want as we want it */
		return $this->r;
	}

	/* Get all status */
	public function GetStatus()
	{
		if (isset($this->query['GetStatus']) && !empty($this->query['GetStatus']))
			return $this->query['GetStatus'];

			$this->data['status']->Params($query_params);
			$this->data['status']->GetData();

			if (!empty($this->data['status']->DataResult()))
			{
				$this->valid = true;
				$this->AddToContext('GetStatus', $this->data['status']->DataResult());
				return $this->data['status']->DataResult();
			}

			else
				return false;
	}

	/* Methods for the status table */

	public function GetStatusUserOwner($var)
	{
		return $this->GetReturn('GetStatus', 'owner_id', $var);
	}

	public function GetStatusUserPoster($var)
	{
		return $this->GetReturn('GetStatus', 'poster_id', $var);
	}

	public function GetStatusID($var)
	{
		return $this->GetReturn('GetStatus', 'id', $var);
	}

	public function GetStatusUserTime($var)
	{
		return $this->GetReturn('GetStatus', 'time', $var);
	}

	/* Get All the comments */
	public function GetComments()
	{
		if (isset($this->query['GetComments']) && !empty($this->query['GetComments']))
			return $this->query['GetComments'];

			$this->data['comments']->Params($query_params);
			$this->data['comments']->GetData();

			if (!empty($this->data['status']->DataResult()))
			{
				$this->valid = true;
				$this->AddToContext('GetComments', $this->data['comments']->DataResult());
				return $this->data['comments']->DataResult();
			}

			else
				return false;
	}

	/* Methods for the comments table */

	public function GetCommentsID($var)
	{
		return $this->GetReturn('GetComments', 'id', $var);
	}

	public function GetCommentsStatusID($var)
	{
		return $this->GetReturn('GetComments', 'status_id', $var);
	}

	public function GetCommentsStatusOwnerID($var)
	{
		return $this->GetReturn('GetComments', 'status_owner_id', $var);
	}

	public function GetCommentsPosterCommentID($var)
	{
		return $this->GetReturn('GetComments', 'poster_comment_id', $var);
	}

	public function GetCommentsProfileOwnerID($var)
	{
		return $this->GetReturn('GetComments', 'profile_owner_id', $var);
	}

	public function GetCommentsTime($var)
	{
		return $this->GetReturn('GetComments', 'time', $var);
	}
}