<?php

/**
 * Breeze-Notify.php
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

class Breeze_Notify_Background extends SMF_BackgroundTask
{
	public function execute()
	{
		// Performance my ass...
		$app = new Breeze();
		$app['noti']->call($this->_details);

		unset($app);

		return true;
	}
}
