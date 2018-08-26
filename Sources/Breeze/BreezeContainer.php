<?php

/**
 * BreezeContainer
 *
 * @package Breeze mod
 * @version 1.0.14
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011 - 2018 Jessica González
 * @license //www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeContainer
{
	protected $values = array();

	public function __set($id, $value)
	{
		$this->values[$id] = $value;
	}

	public function __get($id)
	{
		if (!isset($this->values[$id]))
			fatal_lang_error('Breeze_error_no_property', false, array($id));

		if (is_callable($this->values[$id]))
			return $this->values[$id]($this);

		else
			return $this->values[$id];
	}

	public function asShared($callable)
	{
		return function ($c) use ($callable)
		{
			static $object;

			if (is_null($object))
				$object = $callable($c);

			return $object;
		};
	}
}