<?php

declare(strict_types=1);

/**
 * BreezeAlerts
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2019, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

namespace Breeze;

if (!defined('SMF'))
	die('No direct access...');

class BreezeAlerts
{
	protected $_alerts;

	protected $_app;

	protected $_usersData;

	protected $_scriptUrl;

	public function __construct(Breeze $app)
	{
		$this->_app = $app;

		// We are gonna need some alerts language strings...
		$this->_app['tools']->loadLanguage('alerts');
	}

	public function call(&$alerts): void
	{
		global $memberContext;

		// Don't rely on Profile-View loading the senders data because we need some custom_profile stuff and we need to load other user's data anyway.
		$toLoad = [];

		foreach ($alerts as $id => $a)
			if (false !== strpos($a['content_type'], $this->_app->txtpattern) && !empty($a['extra']['toLoad']))
				$toLoad = array_merge($toLoad, $a['extra']['toLoad']);

		if (!empty($toLoad))
			$this->_app['tools']->loadUserInfo($toLoad, $returnID = false);

		// Pass the people's data.
		$this->_usersData = $memberContext;

		$this->_alerts = $alerts;

		// What type are we gonna handle? oh boy there are a lot!
		foreach ($alerts as $id => $a)
			if (false !== strpos($a['content_type'], $this->_app->txtpattern))
			{
				// Need to remove the Breeze identifier.
				$a['content_type'] = str_replace($this->_app->txtpattern, '', $a['content_type']);

				// Append the sender's avatar if there is one.
				$alerts[$id]['sender'] = !empty($this->_usersData[$a['sender_id']]) ? $this->_usersData[$a['sender_id']] : [];

				if (method_exists($this, $a['content_type']) && !empty($this->_alerts[$id]['extra']) && is_array($this->_alerts[$id]['extra']))
					$alerts[$id]['text'] = $this->{$a['content_type']}($id);

				else
					$alerts[$id]['text'] = '';
			}
	}

	// Weird name, I know...
	protected function status_owner($id)
	{
		return $this->_app['tools']->parser($this->_app['tools']->text('alert_status_owner'), [
		    'href' => $this->_app['tools']->scriptUrl . '?action=wall;sa=single;u=' . $this->_alerts[$id]['extra']['owner'] .
		    ';bid=' . $this->_alerts[$id]['content_id'],
		    'poster' => $this->_usersData[$this->_alerts[$id]['extra']['poster']]['link'],
		]);
	}

	protected function comment_status_owner($id)
	{
		// There are multiple variants of this same alert, however, all that logic was already decided elsewhere...
		return $this->_app['tools']->parser($this->_app['tools']->text('alert_' . $this->_alerts[$id]['extra']['text']), [
		    'href' => $this->_app['tools']->scriptUrl . '?action=wall;sa=single;u=' . $this->_alerts[$id]['extra']['wall_owner'] .
		    ';bid=' . $this->_alerts[$id]['extra']['status_id'] . ';cid=' . $this->_alerts[$id]['content_id'] . '#comment_id_' . $this->_alerts[$id]['content_id'],
		    'poster' => $this->_usersData[$this->_alerts[$id]['extra']['poster']]['link'],
		    'status_poster' => $this->_usersData[$this->_alerts[$id]['extra']['status_owner']]['link'],
		    'wall_owner' => $this->_usersData[$this->_alerts[$id]['extra']['wall_owner']]['link'],
		]);
	}

	protected function comment_profile_owner($id)
	{
		return $this->_app['tools']->parser($this->_app['tools']->text('alert_' . $this->_alerts[$id]['extra']['text']), [
		    'href' => $this->_app['tools']->scriptUrl . '?action=wall;sa=single;u=' . $this->_alerts[$id]['extra']['wall_owner'] .
		    ';bid=' . $this->_alerts[$id]['extra']['status_id'] . ';cid=' . $this->_alerts[$id]['content_id'] . '#comment_id_' . $this->_alerts[$id]['content_id'],
		    'poster' => $this->_usersData[$this->_alerts[$id]['extra']['poster']]['link'],
		    'status_poster' => $this->_usersData[$this->_alerts[$id]['extra']['status_owner']]['link'],
		    'wall_owner' => $this->_usersData[$this->_alerts[$id]['extra']['wall_owner']]['link'],
		]);
	}

	protected function like($id)
	{
		return $this->_app['tools']->parser($this->_app['tools']->text('alert_' . $this->_alerts[$id]['extra']['text']), [
		    'href' => $this->_app['tools']->scriptUrl . '?action=wall;sa=single;u=' . $this->_alerts[$id]['extra']['wall_owner'] .
		    ';bid=' . $this->_alerts[$id]['extra']['status_id'] . (!empty($this->_alerts[$id]['extra']['comment_id']) ? (';cid=' . $this->_alerts[$id]['content_id'] . '#comment_id_' . $this->_alerts[$id]['content_id']) : ''),
		    'poster' => $this->_usersData[$this->_alerts[$id]['sender_id']]['link'],
		    'type' => $this->_alerts[$id]['extra']['like_type'],
		]);
	}

	protected function mention($id)
	{
		$toParse = [
		    'poster' => $this->_usersData[$this->_alerts[$id]['sender_id']]['link'],
		    'url' => $this->_app['tools']->scriptUrl . $this->_alerts[$id]['extra']['url'],
		];

		// Is there a wall owner?
		if (!empty($this->_alerts[$id]['extra']['profile_owner']))
			$toParse['wall_owner'] = $this->_usersData[$this->_alerts[$id]['extra']['profile_owner']]['link'];

		return $this->_app['tools']->parser($this->_app['tools']->text('alert_' . $this->_alerts[$id]['extra']['text']), $toParse);
	}

	protected function buddyConfirm($id)
	{
		// Need this.
		if (empty($this->_alerts[$id]['extra']['text']))
			return;

		// Build the link.
		$confirmLink = $this->_app['tools']->scriptUrl . '?action=buddy;sa=confirm;sender=' . $this->_alerts[$id]['sender_id'] . ';aid=' . $id;

		// Gotta do some magic.
		return $this->_app['tools']->parser($this->_app['tools']->text('alert_buddy_' . $this->_alerts[$id]['extra']['text']), [
		    'href' => $confirmLink,
		    'sender' => $this->_usersData[$this->_alerts[$id]['extra']['sender']]['link'],
		    'receiver' => $this->_usersData[$this->_alerts[$id]['extra']['receiver']]['link'],
		]);
	}
}
