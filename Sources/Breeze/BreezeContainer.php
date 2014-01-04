<?php

/**
 * BreezeContainer
 *
 * The purpose of this file is to create all dependencies on demand, in a centered, unique class
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

/*
* Version: MPL 1.1
*
* The contents of this file are subject to the Mozilla Public License Version
* 1.1 (the "License"); you may not use this file except in compliance with
* the License. You may obtain a copy of the License at
* http://www.mozilla.org/MPL/
*
* Software distributed under the License is distributed on an "AS IS" basis,
* WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
* for the specific language governing rights and limitations under the
* License.
*
* The Original Code is http://missallsunday.com code.
*
* The Initial Developer of the Original Code is
* Jessica González.
* Portions created by the Initial Developer are Copyright (c) 2012
* the Initial Developer. All Rights Reserved.
*
* Contributor(s):
*
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