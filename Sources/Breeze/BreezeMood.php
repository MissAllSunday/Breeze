<?php

/**
 * BreezeMood
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeMood
{
	protected $_app;

	function __construct($app)
	{
		$this->_app = $app;
	}

	function call($app)
	{
		// Crud actions
		$subActions = array(
			'create',
			'read',
			'update',
			'delete',
		);
	}
}
