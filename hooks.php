<?php

/**
 * Breeze_
 *
 * The purpose of this file is
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

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');

	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	/* Everybody likes hooks */
	$hooks = array(
		'integrate_pre_include' => '$sourcedir/Breeze/Breeze.php',
		/* 'integrate_menu_buttons' => 'Breeze::wallMenu', */
		'integrate_actions' => 'Breeze::actions',
		'integrate_load_permissions' => 'Breeze::permissions',
		'integrate_admin_areas' => 'Breeze::admin',
		'integrate_profile_areas' => 'Breeze::profileInfo',
		'integrate_load_theme' => 'Breeze::notificationStream',
	);

	$call = 'add_integration_function';

	foreach ($hooks as $hook => $function)
		$call($hook, $function);