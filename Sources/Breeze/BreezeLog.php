<?php

/**
 * BreezeLog
 *
 * The purpose of this file is to load and show the recent activity from specific IDs
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

class BreezeLog
{
	protected $result = array();
	protected $log = array();

	function __construct($settings, $text, $tools, $query)
	{
		$this->_settings = $settings;
		$this->_text = $text;
		$this->_tools = $tools;
		$this->_query = $query;
	}

	public function getActivity($user)
	{
		// The usual check...
		if (empty($user))
			return false;

		// Work with arrays.
		$user = (array) $user;
		$user = array_unique($user);

		// Lets make queries!
		$this->log = $this->_query->getActivityLog($user);

		// Nada? :(
		if (empty($this->log))
			return false;

		// Lets decide what should we do with these... call a method or pass it straight?
		foreach ($this->log as $id => $entry)
			{
				// Send all data available!
				$this->result[$id] = $entry;

				// If there is a method, call it
				if (in_array($entry['type'], get_class_methods(__CLASS__)))
				{
					$entry['content'] = json_decode($entry['content'], true);
					$this->result[$id]['content'] = $this->$entry['type']($entry);
				}

				// No? then pass the content
				else if (!empty($entry['content']))
					$this->result[$id]['content'] = $entry['content'];
			}

		// If everything went well, return the final result
		return !empty($this->result) ? $this->result : false;
	}

	protected function logComment($entry)
	{
		global $scripturl;

		// We're gonna return an array of info.
		$return = array();

		// Load the users data, one fine day I will count how many times I typed this exact sentence...
		$loadedUsers = $this->_query->loadMinimalData(array_unique(array($entry['content']['status_owner_id'], $entry['content']['poster_id'], $entry['content']['profile_id'])));

		//Posting on your own wall?
		$own = $entry['content']['status_owner_id'] == $entry['content']['poster_id'];

		if ($own)
			$return['message'] = $loadedUsers[$entry['content']['poster_id']]['link'] .' '. $this->_text->getText('logComment_own_'. $loadedUsers[$entry['content']['poster_id']]['gender']);

		else
			$return['message'] = $loadedUsers[$entry['content']['poster_id']]['link'] .' '. sprintf($this->_text->getText('logComment'), $loadedUsers[$entry['content']['status_owner_id']]['link']);

		// Build a nice link to the status.
		$return['link'] = '<a href="'. $scripturl .'?action=wall;sa=single;bid='. $entry['content']['status_id'] .';cid='. $entry['content']['id'] .'#comment_id_'. $entry['content']['id'] .'">'. $this->_text->getText('logComment_view') . '</a>';

		return $return;
	}

	protected function logStatus($entry)
	{
		global $scripturl;

		// Load the users data, one fine day I will count how many times I typed this exact sentence...
		$loadedUsers = $this->_query->loadMinimalData(array_unique(array($entry['content']['owner_id'], $entry['content']['poster_id'],)));

		//Posting on your own wall?
		$own = $entry['content']['owner_id'] == $entry['content']['poster_id'];

		if ($own)
			$return['message'] = $loadedUsers[$entry['content']['poster_id']]['link'] .' '. $this->_text->getText('logStatus_own');

		else
			$return['message'] = $loadedUsers[$entry['content']['poster_id']]['link'] .' '. sprintf($this->_text->getText('logStatus'), $entry['content']['owner_id']);

		// Build a link to the status.
		$return['link'] = '<a href="'. $scripturl .'?action=wall;sa=single;bid='. $entry['content']['id'] .'#status_id_'. $entry['content']['id'] .'">'. $this->_text->getText('logStatus_view') . '</a>';

		return $return;
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
