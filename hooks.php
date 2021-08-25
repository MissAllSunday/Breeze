<?php

declare(strict_types=1);


	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');

	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	// Everybody likes hooks
	$hooks = [
	    'integrate_pre_include' => '$boarddir/breezeVendor/autoload.php',
	    'integrate_menu_buttons' => '\Breeze\Breeze::menu#',
	    'integrate_actions' => '\Breeze\Breeze::actions#',
	    'integrate_load_permissions' => '\Breeze\Breeze::permissionsWrapper#',
	    'integrate_admin_areas' => '\Breeze\Breeze::adminMenuWrapper#',
	    'integrate_pre_profile_areas' => '\Breeze\Breeze::profileMenuWrapper#',
	    'integrate_valid_likes' => '\Breeze\Breeze::updateLikesWrapper#',
	    // 'integrate_find_like_author' => '\Breeze\Breeze::handleLikes#',
	    'integrate_member_context' => '\Breeze\Breeze::displayMoodWrapper#',
	    // 'integrate_load_custom_profile_fields' => '\Breeze\Breeze::displayMoodProfileWrapper#',
	    // 'integrate_fetch_alerts' => '\Breeze\Breeze::alerts#',
	    'integrate_alert_types' => '\Breeze\Breeze::alertsPrefWrapper#',
	    'integrate_profile_popup' => '\Breeze\Breeze::profilePopUpWrapper#',
	];

	foreach ($hooks as $hook => $function)
		add_integration_function($hook, $function);
