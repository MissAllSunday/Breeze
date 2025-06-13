<?php

declare(strict_types=1);

/**
 * Breeze-Notify.php
 *
 * @package Breeze mod
 * @version 1.1
 * @author Michel Mendiola <suki@missallsunday.com>
 * @copyright Copyright (c) 2019, Michel Mendiola
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

class Breeze_Notify_Background extends SMF_BackgroundTask
{
	public function execute()
	{
		// Performance my ass...
		$app = new \Breeze\Breeze();
		$app['noti']->call($this->_details);

		unset($app);

		return true;
	}
}
