<?php

/**
 * @package breeze mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2011 Suki
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');

	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	global $context;

	// Everybody likes hooks
	$hooks = array(
		'integrate_pre_include' => '$sourcedir/Breeze/Breeze.php',
		'integrate_menu_buttons' => 'Breeze::Wall_Menu',
		'integrate_actions' => 'Breeze::Action_Hook',
		'integrate_load_permissions' => 'Breeze::Permissions',
		'integrate_admin_areas' => 'Breeze::Admin_Button',
		'integrate_profile_areas' => 'Breeze::Profile_Info'
	);

	if (!empty($context['uninstalling']))
		$call = 'remove_integration_function';

	else
		$call = 'add_integration_function';

	foreach ($hooks as $hook => $function)
		$call($hook, $function);

?>