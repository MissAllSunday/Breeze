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

class BreezeController
{
	public $app = array();
	protected $_services = array('admin' 'ajax', 'data', 'display', 'form', 'log', 'mention', 'notifications', 'parser', 'query', 'tools', 'user', 'userInfo', 'wall',);
	protected $_call = 'Breeze';

	public function __construct()
	{
		$this->app = new Pimple;
		$this->set();
	}

	protected function set()
	{
		foreach($this->_services as $s)
		{
			$call = 'Breeze'. ucfirst($s);
			$this->app[$s] =  function ($c)
			{
				$call = $this->_call . ucfirst($s);
				return new $call($c);
			};
		}
	}

	public function get($var)
	{
		return $this->app[$var];
	}
}
