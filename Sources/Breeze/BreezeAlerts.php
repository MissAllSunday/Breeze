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
		global $memberContext;

		$this->_usersData = $memberContext;
		$this->_app = $app;
	}

	public function call($alerts)
	{
		$this->_alerts = $alerts;

		// What type are we gonna handle? oh boy there are a lot!
		foreach ($alerts as $id => $a)
			if(method_exists($this, $a['content_type']))
				$this->$a['content_type']($id);

		// Fill out an error, dunno...

		// Don't forget to return the array.
		return $this->_alerts;
	}

	// Weird name, I know...
	protected function status_owner($id)
	{
		if (empty($id))
			return;

		// Build a link to it.
		$link = = $this->_app['tools']->scriptUrl . '?action=wall;sa=single;u=' . $this->_alerts[$id]['id_member'] .
			';bid=' . $this->_alerts[$id]['content_id'];
	}

	protected function comment_status_owner()
	{

	}

	protected function comment_profile_owner()
	{

	}


}
