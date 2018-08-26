<?php

/**
 * BreezeDispatcher
 *
 * @package Breeze mod
 * @version 1.0.14
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011 - 2018 Jessica González
 * @license //www.mozilla.org/MPL/MPL-1.1.html
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
		global $breezeController;

		if (empty($breezeController))
			$breezeController = new BreezeController();

		$data = Breeze::data('get');

		$actions = array(
			'breezeajax' => array('BreezeAjax' , 'call'),
			'wall' => array('BreezeWall', 'call'),
			// 'buddy' => array('BreezeBuddy', 'buddy'),  @todo for next version
		);

		// Want to add some more goodies?
		call_integration_hook('integrate_breeze_actions', array(&$actions));

		$do = $data->get('action');

		if (isset($actions[$do]))
		{
			$controller = $actions[$do][0];

			$method = $actions[$do][1];

			// Lets do some checks...
			if (!method_exists($controller, $method) || !is_callable($controller, $method))
				fatal_lang_error('Breeze_error_no_valid_action', false);

			// Create the instance
			$object = new $controller($breezeController->get('tools'), $breezeController->get('display'),  $breezeController->get('parser'), $breezeController->get('query'), $breezeController->get('notifications'), $breezeController->get('mention'), $breezeController->get('log'));

			// Lets call it
			$object->{$method}();
		}

		else
			fatal_lang_error('Breeze_error_no_valid_action', false);
	}
}
