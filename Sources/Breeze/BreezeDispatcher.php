<?php

/**
 * BreezeDispatcher
 *
 * The purpose of this file is, handles all Breeze actions and calls their respective methods
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2012, Jessica González
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
	die('Hacking attempt...');

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
		$container = new BreezeContainer();

		/* Globals */
		$container->globals = $container->asShared(function ($c)
		{
			return new BreezeGlobals();
		});

		/* Settings */
		$container->settings = $container->asShared(function ($c)
		{
			return new BreezeSettings();
		});

		/* Text */
		$container->text = $container->asShared(function ($c)
		{
			return new BreezeText();
		});

		/* Query */
		$container->query = $container->asShared(function ($c)
		{
			return new BreezeQuery($c->settings, $c->tools, $c->parser, $c->text);
		});

		/* Form */
		$container->form = $container->asShared(function ($c)
		{
			return new BreezeQuery($c->text);
		});

		$actions = array(
			'breezeajax' => array('BreezeAjax' , 'call'),
			'wall' => array('BreezeGeneral', 'call'),
			'buddy' => array('BreezeBuddy', 'buddy'),
		);

		if (in_array($sglobals->getValue('action'), array_keys($actions)))
		{
			$controller_name = $actions[$sglobals->getValue('action')][0];
			$controller = new $controller_name();

			/* Lets call the method */
			$method_name = $actions[$sglobals->getValue('action')][1];
			call_user_func_array(array($controller, $method_name), array());
		}
	}
}