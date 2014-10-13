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

	public function __construct($app)
	{
		$this->_app = $app;
	}

	public function call($alerts)
	{
		$this->_alerts = $alerts;

		// What type are we gonna handle? oh boy there are a lot!
		if(method_exists($this, $alerts['content_type']))
			$this->$alerts['content_type']();

		// Fill out an error, dunno...

		// Don't forget to return the array.
		return $this->_alerts;
	}

	// Weird name, I know...
	protected function status_owner()
	{

	}

	protected function comment_status_owner()
	{

	}

	protected function comment_profile_owner()
	{

	}


}
