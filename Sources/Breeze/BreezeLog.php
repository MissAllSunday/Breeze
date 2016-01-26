<?php

/**
 * BreezeLog
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2015, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeLog
{
	protected $_users = array();
	protected $_data = array();
	protected $_app;
	public $alerts = array('cover', 'mood', 'like', 'status', 'comment', 'topic', 'buddyConfirmation');

	function __construct($app)
	{
		$this->_app = $app;

		// We are gonna need some alerts language strings...
		$this->_app['tools']->loadLanguage('alerts');
	}

	public function get($users, $maxIndex, $start)
	{
		if (empty($users))
			return array();

		$this->_users = (array) $users;
		$this->_logCount =  $this->_app['query']->logCount($this->_users);

		$this->_data = $this->_app['query']->getLog($this->_users, $maxIndex, $start);

		// Perhaps modify the main data before been processed.
		call_integration_hook('integrate_breeze_log_before', array(&$this->_data, &$this->_users, &$this->_logCount, &$this->alerts));

		// Parse the raw data.
		$this->call();

		// Or after?
		call_integration_hook('integrate_breeze_log_after', array($this->_data, $this->_users, $this->_logCount, $this->alerts));

		// Return the formatted data.
		return array(
			'count' => $this->_logCount,
			'data' => $this->_data,
		);
	}

	protected function call()
	{
		global $context;

		// Kinda need this...
		if (empty($this->_data) || !is_array($this->_data))
			return;

		// The users to load.
		$toLoad = array();

		// Get the users before anything gets parsed.
		foreach ($this->_data as $id => $data)
			$toLoad = array_merge($toLoad, $this->_data[$id]['extra']['toLoad']);

		if (!empty($toLoad))
			$this->_app['tools']->loadUserInfo($toLoad, false);

		// Pass people's data.
		$this->_usersData = $context['Breeze']['user_info'];

		foreach ($this->_data as $id => $data)
		{
			// Get the right gender stuff.
			$this->_data[$id]['gender'] = !empty($this->_usersData[$this->_data[$id]['member']]['options']['cust_gender']) ? $this->_usersData[$this->_data[$id]['member']]['options']['cust_gender'] : 'None';

			$this->_data[$id]['gender_possessive'] = $this->_app['tools']->text('alert_gender_possessive_'. $this->_data[$id]['gender']) ? $this->_app['tools']->text('alert_gender_possessive_'. $this->_data[$id]['gender']) : $this->_app['tools']->text('alert_gender_possessive_None');

			// Make sure we have a valid method for this and valid data too!
			if (method_exists($this, $data['content_type']) && !empty($this->_data[$id]['extra']) && is_array($this->_data[$id]['extra']) && empty($this->_data[$id]['text']))
				$this->{$data['content_type']}($id);

			// Add an empty text string if the method failed to properly set one...
			elseif (empty($this->_data[$id]['text']))
				$this->_data[$id]['text'] = '';
		}
	}

	public function mood($id)
	{
		// Add the custom icon.
		$this->_data[$id]['icon'] = 'smile-o';

		// Get the mood.
		$this->_data[$id]['extra']['moodHistory'] = json_decode($this->_data[$id]['extra']['moodHistory'], true);
		$mood = !empty($this->_data[$id]['extra']['moodHistory']['id']) ? $this->_app['query']->getMoodByID($this->_data[$id]['extra']['moodHistory']['id'], true) : array();

		// Return the formatted string.
		$this->_data[$id]['text'] = $this->_app['tools']->parser($this->_app['tools']->text('alert_mood'), array(
			'poster' => $this->_usersData[$this->_data[$id]['member']]['link'],
			'gender_possessive' => $this->_data[$id]['gender_possessive'],
			'image' => !empty($mood) && !empty($mood['image_html']) ? $mood['image_html'] : '',
		));
	}

	public function cover($id)
	{
		// Add the custom icon.
		$this->_data[$id]['icon'] = 'photo';

		$this->_data[$id]['text'] = $this->_app['tools']->parser($this->_app['tools']->text('alert_cover'), array(
			'poster' => $this->_usersData[$this->_data[$id]['member']]['link'],
			'gender_possessive' => $this->_data[$id]['gender_possessive'],
			'image' => '<img src="'. $this->_app['tools']->scriptUrl .'?action=breezecover;thumb;u='. $this->_data[$id]['member'] .'" />',
		));
	}

	public function status($id)
	{
		// Add the custom icon.
		$this->_data[$id]['icon'] = 'comment';

		$this->_data[$id]['text'] = $this->_app['tools']->parser($this->_app['tools']->text($this->_data[$id]['extra']['buddy_text']), array(
			'href' => $this->_app['tools']->scriptUrl . '?action=wall;sa=single;u=' . $this->_data[$id]['extra']['wall_owner'] .
			';bid=' . $this->_data[$id]['content_id'],
			'poster' => $this->_usersData[$this->_data[$id]['extra']['poster']]['link'],
			'wall_owner' => $this->_usersData[$this->_data[$id]['extra']['wall_owner']]['link'],
		));
	}

	public function comment($id)
	{
		// Add the custom icon.
		$this->_data[$id]['icon'] = 'comments';

		$this->_data[$id]['text'] = $this->_app['tools']->parser($this->_app['tools']->text($this->_data[$id]['extra']['buddy_text']), array(
			'href' => $this->_app['tools']->scriptUrl . '?action=wall;sa=single;u=' . $this->_data[$id]['extra']['wall_owner'] .';bid=' . $this->_data[$id]['extra']['status_id'],
			'poster' => $this->_usersData[$this->_data[$id]['extra']['poster']]['link'],
			'status_owner' => $this->_usersData[$this->_data[$id]['extra']['status_owner']]['link'],
			'wall_owner' => $this->_usersData[$this->_data[$id]['extra']['wall_owner']]['link'],
		));
	}

	public function like($id)
	{
		// Add the custom icon.
		$this->_data[$id]['icon'] = 'thumbs-o-up';

		// This heavily relies on the liked content data.
		if (empty($this->_data[$id]['extra']['contentData']))
			return;

		// If there is no status_id key it means the content was a comment.
		$url = !empty($this->_data[$id]['extra']['contentData']['status_id']) ? ($this->_data[$id]['extra']['contentData']['status_id'] .';cid='. $this->_data[$id]['extra']['contentData']['id']) : $this->_data[$id]['extra']['contentData']['id'];

		$this->_data[$id]['text'] = $this->_app['tools']->parser($this->_app['tools']->text('alert_like_buddy'), array(
			'poster' => $this->_usersData[$this->_data[$id]['member']]['link'],
			'href' => $this->_app['tools']->scriptUrl . '?action=wall;sa=single;u=' . $this->_data[$id]['extra']['contentData']['profile_id'] .';bid=' . $url,
			'contentOwner' => $this->_usersData[$this->_data[$id]['extra']['contentData']['poster_id']]['link'],
			'type' => $this->_data[$id]['extra']['type'],
		));
	}

	public function topic($id)
	{
		$this->_data[$id]['icon'] = 'comment-o';

		$this->_data[$id]['text'] = $this->_app['tools']->parser($this->_app['tools']->text('alert_topic'), array(
			'href' => $this->_app['tools']->scriptUrl . '?topic=' . $this->_data[$id]['content_id'],
			'poster' => $this->_usersData[$this->_data[$id]['member']]['link'],
			'subject' => $this->_data[$id]['extra']['subject'],
		));
	}

	public function buddyConfirmed($id)
	{
		// Add the custom icon.
		$this->_data[$id]['icon'] = 'users';

		$this->_data[$id]['text'] = $this->_app['tools']->parser($this->_app['tools']->text('alert_buddy_done'), array(
			'sender' => $this->_usersData[$this->_data[$id]['extra']['sender']]['link'],
			'receiver' => $this->_usersData[$this->_data[$id]['extra']['receiver']]['link'],
		));
	}
}
