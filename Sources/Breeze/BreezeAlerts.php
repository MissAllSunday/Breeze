<?php

/**
 * BreezeAlerts
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeAlerts
{
	protected $_alerts;
	protected $_app;
	protected $_usersData;
	protected $_scriptUrl;

	public function __construct($app)
	{
		$this->_app = $app;

		// We are gonna need some alerts language strings...
		$this->_app['tools']->loadLanguage('alerts');
	}

	public function call(&$alerts)
	{
		global $memberContext;

		// Don't rely on Profile-View loading the senders data because we need some custom_profile stuff and we need to load other user's data anyway.
		$toLoad = array();

		$this->_alerts = $alerts;

		foreach ($alerts as $id => $a)
			if (strpos($a['content_type'], Breeze::$txtpattern) !== false && !empty($a['extra']['toLoad']))
				$toLoad = array_merge($toLoad, $a['extra']['toLoad']);

		if (!empty($toLoad))
			$this->_app['tools']->loadUserInfo($toLoad, $returnID = false);

		// Pass the people's data.
		$this->_usersData = $memberContext;

		// What type are we gonna handle? oh boy there are a lot!
		foreach ($alerts as $id => $a)
			if (strpos($a['content_type'], Breeze::$txtpattern) !== false)
			{
				$a['content_type'] = str_replace(Breeze::$txtpattern, '', $a['content_type']);

				if(method_exists($this, $a['content_type']) && !empty($this->_alerts[$id]['extra'] && is_array($this->_alerts[$id]['extra'])))
					$alerts[$id]['text'] = $this->$a['content_type']($id);

				else
					$alerts[$id]['text'] = '';
			}
	}

	// Weird name, I know...
	protected function status_owner($id)
	{
		return $this->_app['tools']->parser($this->_app['tools']->text('alert_status_owner'), array(
			'href' => $this->_app['tools']->scriptUrl . '?action=wall;sa=single;u=' . $this->_alerts[$id]['extra']['owner'] .
			';bid=' . $this->_alerts[$id]['content_id'],
			'poster' => $this->_usersData[$this->_alerts[$id]['extra']['poster']]['link'],
		));
	}

	protected function comment_status_owner($id)
	{
		// There are multiple variants of this same alert, however, all that logic was already decided elsewhere...
		return $this->_app['tools']->parser($this->_app['tools']->text('alert_'. $this->_alerts[$id]['extra']['text']), array(
			'href' => $this->_app['tools']->scriptUrl . '?action=wall;sa=single;u=' . $this->_alerts[$id]['extra']['wall_owner'] .
			';bid=' . $this->_alerts[$id]['extra']['status_id'] .';cid=' . $this->_alerts[$id]['content_id'] .'#comment_id_' . $this->_alerts[$id]['content_id'],
			'poster' => $this->_usersData[$this->_alerts[$id]['extra']['poster']]['link'],
			'status_poster' => $this->_usersData[$this->_alerts[$id]['extra']['status_owner']]['link'],
			'wall_owner' => $this->_usersData[$this->_alerts[$id]['extra']['wall_owner']]['link'],
		));
	}

	protected function comment_profile_owner($id)
	{
		return $this->_app['tools']->parser($this->_app['tools']->text('alert_'. $this->_alerts[$id]['extra']['text']), array(
			'href' => $this->_app['tools']->scriptUrl . '?action=wall;sa=single;u=' . $this->_alerts[$id]['extra']['wall_owner'] .
			';bid=' . $this->_alerts[$id]['extra']['status_id'] .';cid=' . $this->_alerts[$id]['content_id'] .'#comment_id_' . $this->_alerts[$id]['content_id'],
			'poster' => $this->_usersData[$this->_alerts[$id]['extra']['poster']]['link'],
			'status_poster' => $this->_usersData[$this->_alerts[$id]['extra']['status_owner']]['link'],
			'wall_owner' => $this->_usersData[$this->_alerts[$id]['extra']['wall_owner']]['link'],
		));
	}

	protected function like($id)
	{
		return $this->_app['tools']->parser($this->_app['tools']->text('alert_'. $this->_alerts[$id]['extra']['text']), array(
			'href' => $this->_app['tools']->scriptUrl . '?action=wall;sa=single;u=' . $this->_alerts[$id]['extra']['wall_owner'] .
			';bid=' . $this->_alerts[$id]['extra']['status_id'] .(!empty($this->_alerts[$id]['extra']['comment_id']) ? (';cid=' . $this->_alerts[$id]['content_id'] .'#comment_id_' . $this->_alerts[$id]['content_id']) : ''),
			'poster' => $this->_usersData[$this->_alerts[$id]['sender_id']]['link'],
		));
	}

	protected function mention($id)
	{
		$toParse = array(
			'poster' => $this->_usersData[$this->_alerts[$id]['extra']['id_member_started']]['link'],
			'url' => $this->_app['tools']->scriptUrl . $this->_alerts[$id]['extra']['url'],
		);

		// Is there a wall owner?
		if (!empty($this->_alerts[$id]['extra']['profile_owner']))
			$toParse['wall_owner'] = $this->_usersData[$$this->_alerts[$id]['extra']['profile_owner']]['link'];

		return $this->_app['tools']->parser($this->_app['tools']->text('alert_'. $this->_alerts[$id]['extra']['text']), $toParse);
	}
}
