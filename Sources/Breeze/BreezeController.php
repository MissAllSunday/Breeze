<?php

/**
 * BreezeController
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica Gonzalez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeController extends Pimple
{
	public $app;
	protected $_services = array('admin', 'ajax', 'display', 'form', 'log', 'mention', 'notifications', 'parser', 'query', 'tools', 'user', 'userInfo', 'wall',);

	public function __construct()
	{
		parent::__construct();
		$this->set();
	}

	protected function set()
	{
		foreach($this->_services as $s)
		{
			$this[$s] = function ($c) use ($s)
			{
				$call = 'Breeze'. ucfirst($s);
				return new $call($c);
			};
		}
	}
}
