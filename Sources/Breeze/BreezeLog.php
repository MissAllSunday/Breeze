<?php

/**
 * BreezeLog
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica Gonzalez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeLog
{
	protected $result = array();
	protected $log = array();

	function __construct($tools, $query)
	{
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
				{
					// All templates expects an array with at least two keys, message and link, I'm lazy so I don't always provide those... hence this check ;)
					if (!is_array($entry['content']))
						$entry['content'] = array('message' => $entry['content']);

					else
						$this->result[$id]['content'] = $entry['content'];
				}
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

		$gender = !empty($loadedUsers[$entry['content']['poster_id']]['gender']) ? $loadedUsers[$entry['content']['poster_id']]['gender'] : '0';

		//Posting on your own wall?
		$own = $entry['content']['status_owner_id'] == $entry['content']['poster_id'];

		if ($own)
			$return['message'] = $loadedUsers[$entry['content']['poster_id']]['link'] .' '. $this->_tools->text('logComment_own_'. $gender);

		else
			$return['message'] = $loadedUsers[$entry['content']['poster_id']]['link'] .' '. sprintf($this->_tools->text('logComment'), $loadedUsers[$entry['content']['status_owner_id']]['link']);

		// Build a nice link to the status.
		$return['link'] = '<a href="'. $scripturl .'?action=wall;sa=single;bid='. $entry['content']['status_id'] .';cid='. $entry['content']['id'] .'#comment_id_'. $entry['content']['id'] .'">'. $this->_tools->text('logComment_view') . '</a>';

		return $return;
	}

	protected function logStatus($entry)
	{
		global $scripturl;

		// Load the users data, one fine day I will count how many times I typed this exact sentence...
		$loadedUsers = $this->_query->loadMinimalData(array_unique(array($entry['content']['owner_id'], $entry['content']['poster_id'],)));

		//Posting on your own wall?
		$own = $entry['content']['owner_id'] == $entry['content']['poster_id'];

		// Get the right text string
		$gender = !empty($loadedUsers[$entry['content']['poster_id']]['gender']) ? $loadedUsers[$entry['content']['poster_id']]['gender'] : '0';
		$logStatusString = 'logStatus_own_'. $gender;

		if ($own)
			$return['message'] = $loadedUsers[$entry['content']['poster_id']]['link'] .' '. $this->_tools->text($logStatusString);

		else
			$return['message'] = $loadedUsers[$entry['content']['poster_id']]['link'] .' '. sprintf($this->_tools->text('logStatus'), $loadedUsers[$entry['content']['owner_id']]['link']);

		// Build a link to the status.
		$return['link'] = '<a href="'. $scripturl .'?action=wall;sa=single;bid='. $entry['content']['id'] .'#status_id_'. $entry['content']['id'] .'">'. $this->_tools->text('logStatus_view') . '</a>';

		return $return;
	}

	protected function logTopic($entry)
	{
		global $scripturl;

		return array(
			'message' => $entry['content']['posterName'] .' '. $this->_tools->text('logTopic'),
			'link' => '<a href="'. $scripturl .'?topic='. $entry['content']['topicId'] .'.0">'. $entry['content']['subject'] .'</a>',
		);
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
