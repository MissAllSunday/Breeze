<?php

/**
 * BreezeNoti
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeNoti
{
	public function __construct($app)
	{
		global $smcFunc;

		$this->_app = $app;
		$this->_smcFunc = $smcFunc;
	}

	public function insertMessage($params)
	{

	}
}
