<?php

/**
 * BreezeContainer
 *
 * The purpose of this file is to create all dependencies on demand, in a centered, unique class
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica Gonz�lez <suki@missallsunday.com>
 * @copyright Copyright (c) 2013 Jessica Gonz�lez
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
* Jessica Gonz�lez.
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
			fatal_lang_error('some text here');

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