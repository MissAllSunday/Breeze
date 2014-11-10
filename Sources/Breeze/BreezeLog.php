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

	public function create()
	{

	}

	protected function call()
	{

	}
}
