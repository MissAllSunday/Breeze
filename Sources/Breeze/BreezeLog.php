<?php

/**
 * BreezeLog
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeLog
{
	protected $_result = array();
	protected $_log = array();
	protected $_boards = array();
	protected $_app;

	function __construct($app)
	{
		$this->_app = $app;
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
		$this->_log = $this->_app['query']->getActivityLog($user);

		// Nada? :(
		if (empty($this->_log))
			return false;

		// Get the boards YOU can see
		$this->_boards = $this->_app['query']->wannaSeeBoards();

		// Lets decide what should we do with these... call a method or pass it straight?
		foreach ($this->_log as $id => $entry)
			{
				// Send all data available!
				$this->_result[$id] = $entry;

				// If there is a method, call it
				if (in_array($entry['type'], get_class_methods(__CLASS__)))
				{
					$entry['content'] = json_decode($entry['content'], true);
					$this->_result[$id]['content'] = $this->$entry['type']($entry);

					// Got something?
					if (empty($this->_result[$id]['content']))
						unset($this->_result[$id]);
				}

				// No? then pass the content
				else if (!empty($entry['content']))
				{
					// If there isn't a specific method for this log, Breeze will expect a serialized array with a message and link keys.
					if ($this->_app['tools']->isJson($entry['content']))
						$this->_result[$id]['content'] = json_decode($entry['content'], true);
				}
			}

		// If everything went well, return the final result
		return !empty($this->_result) ? $this->_result : false;
	}

	protected function logComment($entry)
	{
		global $scripturl;

		// We're gonna return an array of info.
		$return = array();

		// Load the users data, one fine day I will count how many times I typed this exact sentence...
		$loadedUsers = $this->_app['query']->loadMinimalData(array_unique(array($entry['content']['status_owner_id'], $entry['content']['poster_id'], $entry['content']['profile_id'])));

		$gender = !empty($loadedUsers[$entry['content']['poster_id']]['gender']) ? $loadedUsers[$entry['content']['poster_id']]['gender'] : '0';

		//Posting on your own wall?
		$own = $entry['content']['status_owner_id'] == $entry['content']['poster_id'];

		if ($own)
			$return['message'] = $loadedUsers[$entry['content']['poster_id']]['link'] .' '. $this->_app['tools']->text('logComment_own_'. $gender);

		else
			$return['message'] = $loadedUsers[$entry['content']['poster_id']]['link'] .' '. sprintf($this->_app['tools']->text('logComment'), $loadedUsers[$entry['content']['status_owner_id']]['link']);

		// Build a nice link to the status.
		$return['link'] = '<a href="'. $scripturl .'?action=wall;sa=single;bid='. $entry['content']['status_id'] .';cid='. $entry['content']['id'] .'#comment_id_'. $entry['content']['id'] .'">'. $this->_app['tools']->text('logComment_view') . '</a>';

		return $return;
	}

	protected function logStatus($entry)
	{
		global $scripturl;

		// Load the users data, one fine day I will count how many times I typed this exact sentence...
		$loadedUsers = $this->_app['query']->loadMinimalData(array_unique(array($entry['content']['owner_id'], $entry['content']['poster_id'],)));

		//Posting on your own wall?
		$own = ($entry['content']['owner_id'] == $entry['content']['poster_id']);

		// Get the right text string
		$gender = !empty($loadedUsers[$entry['content']['poster_id']]['gender']) ? $loadedUsers[$entry['content']['poster_id']]['gender'] : '0';
		$logStatusString = 'logStatus_own_'. $gender;

		if ($own)
			$return['message'] = $loadedUsers[$entry['content']['poster_id']]['link'] .' '. $this->_app['tools']->text($logStatusString);

		else
			$return['message'] = $loadedUsers[$entry['content']['poster_id']]['link'] .' '. sprintf($this->_app['tools']->text('logStatus'), $loadedUsers[$entry['content']['owner_id']]['link']);

		// Build a link to the status.
		$return['link'] = '<a href="'. $scripturl .'?action=wall;sa=single;bid='. $entry['content']['id'] .'#status_id_'. $entry['content']['id'] .'">'. $this->_app['tools']->text('logStatus_view') . '</a>';

		return $return;
	}

	protected function logTopic($entry)
	{
		global $scripturl;

		// Lets see if you can see this topic.
		if (!empty($this->_boards) && !empty($entry['content']['board']) && in_array($entry['content']['board'], $this->_boards))
			return array(
				'message' => $entry['content']['posterName'] .' '. $this->_app['tools']->text('logTopic'),
				'link' => '<a href="'. $scripturl .'?topic='. $entry['content']['topicId'] .'.0">'. $entry['content']['subject'] .'</a>',
			);

		else
			return array();
	}

	public function getLog()
	{
		return $this->_log;
	}

	public function getResult()
	{
		return $this->_result;
	}
}
