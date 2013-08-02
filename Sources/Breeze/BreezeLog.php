<?php

/**
 * BreezeLog
 *
 * The purpose of this file is to load and show the recent activity from specific IDs
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

class BreezeLog
{
	protected $result = array();
	protected $log = array();

	function __construct($query)
	{
		$this->_query = $query;
	}

	public function getActivity($user)
	{
		// The usual check...
		if (empty($user))
			return false;

		// Lets make queries!
		$temp = $this->_query->getActivityLog($user);

		// Nada? :(
		if (empty($temp) || empty($temp[$user]))
			return false;

		else
			$this->log = $temp[$user];

		// Lets decide what should we do with these... call a method or pass it straight?
		foreach ($this->log as $entry)
		{
			// If there is a method, call it
			if (in_array($entry['type'], get_class_methods('BreezeLog')))
				$this->$entry['type']();

			// No? then pass the content
			else if (!empty($entry['content']))
				$this->result[$entry['id']] = $entry['content'];
		}

		// If everything went well, return the final result
		return !empty($this->result) ? $this->result : false;
	}

	public function getLog()
	{
		return $this->log;
	}

	public function getResult()
	{
		return $this->result;
	}
}
