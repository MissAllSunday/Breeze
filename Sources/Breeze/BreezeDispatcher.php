<?php

/**
 * BreezeDispatcher
 *
 * The purpose of this file is, handles all Breeze actions and calls their respective methods
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

abstract class BreezeDispatcher
{
	/**
	 * BreezeDispatcher::__construct()
	 *
	 * @return
	 */
	private function __construct(){}

	static function dispatch()
	{
		$dependency = new BreezeController();
		$sglobals = Breeze::sGlobals('get');

		$actions = array(
			'breezeajax' => array('BreezeAjax' , 'call'),
			'wall' => array('BreezeWall', 'call'),
			// 'buddy' => array('BreezeBuddy', 'buddy'),  @todo for next version
		);

		// Want to add some more goodies?
		call_integration_hook('integrate_breeze_actions', array(&$actions));

		if (in_array($sglobals->getValue('action'), array_keys($actions)))
		{
			$controller = $actions[$sglobals->getValue('action')][0];

			$method = $actions[$sglobals->getValue('action')][1];

			// Lets do some checks...
			if (!method_exists($controller, $method) || !is_callable($controller, $method))
				fatal_lang_error('Breeze_error_no_valid_action', false);

			// Create the instance
			$object = new $controller($dependency->get('tools'), $dependency->get('display'),  $dependency->get('parser'), $dependency->get('query'), $dependency->get('notifications'), $dependency->get('mention'), $dependency->get('log'));

			// Lets call it
			$object->$method();
		}
	}
}
