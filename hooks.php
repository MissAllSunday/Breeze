<?php

/**
 * hooks.php
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');

	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	// Everybody likes hooks
	$hooks = array(
		'integrate_pre_include' => '$sourcedir/Breeze/Breeze.php',
		'integrate_menu_buttons' => '$sourcedir/Breeze/Breeze.php|Breeze::menu#',
		'integrate_actions' => 'Breeze::actions#',
		'integrate_load_permissions' => 'Breeze::permissions#',
		'integrate_admin_areas' => '$sourcedir/Breeze/Breeze.php|Breeze::admin#',
		'integrate_profile_areas' => 'Breeze::profile#',
		'integrate_valid_likes' => 'Breeze::likes#',
		'integrate_find_like_author' => 'Breeze::handleLikes#',
		'integrate_create_topic' => 'Breeze::newTopic#',
		'integrate_prepare_display_context' => '$sourcedir/Breeze/Breeze.php|Breeze::mood#',
		// 'integrate_register_after' => 'Breeze::newRegister',  @todo for SMF 2.1
	);

	foreach ($hooks as $hook => $function)
		add_integration_function($hook, $function);
