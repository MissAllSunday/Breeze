<?php

declare(strict_types=1);

/**
 * hooks.php
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2019, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');

	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	// Everybody likes hooks
	$hooks = [
	    'integrate_pre_include' => '$sourcedir/Breeze/Breeze.php',
	    'integrate_autoload' => '$sourcedir/Breeze/Breeze.php|\Breeze\Breeze::autoLoad#',
	    'integrate_menu_buttons' => '$sourcedir/Breeze/Breeze.php|\Breeze\Breeze::menu#',
	    'integrate_actions' => '\Breeze\Breeze::actions#',
	    'integrate_load_permissions' => '\Breeze\Breeze::permissions#',
	    'integrate_admin_areas' => '$sourcedir/Breeze/Breeze.php|\Breeze\Breeze::admin#',
	    'integrate_pre_profile_areas' => '\Breeze\Breeze::profile#',
	    'integrate_valid_likes' => '\Breeze\Breeze::likes#',
	    'integrate_find_like_author' => '\Breeze\Breeze::handleLikes#',
	    'integrate_member_context' => '$sourcedir/Breeze/Breeze.php|\Breeze\Breeze::mood#',
	    'integrate_load_custom_profile_fields' => '$sourcedir/Breeze/Breeze.php|\Breeze\Breeze::moodProfile#',
	    'integrate_fetch_alerts' => '$sourcedir/Breeze/Breeze.php|\Breeze\Breeze::alerts#',
	    'integrate_alert_types' => '$sourcedir/Breeze/Breeze.php|\Breeze\Breeze::alertsPref#',
	    'integrate_pre_load' => '$sourcedir/Breeze/Breeze.php|\Breeze\Breeze::trackHooks#',
	    'integrate_profile_popup' => '$sourcedir/Breeze/Breeze.php|\Breeze\Breeze::profilePopUp#',
	];

	foreach ($hooks as $hook => $function)
		add_integration_function($hook, $function);
