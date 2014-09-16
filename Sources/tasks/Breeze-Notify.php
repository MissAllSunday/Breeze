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
	function __construct()
	{
		$this->_app = new Breeze();
	}

	public function execute()
	{
	}
}
