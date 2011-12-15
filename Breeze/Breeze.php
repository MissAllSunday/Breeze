<?php

/**
 * Breeze_
 * 
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011, Jessica González
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
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Breeze
{
	public function __construct()
	{
	}

	/**
	 * An attempt to load the static method(s) used across the mod
	 *
	 * @todo Implement some checks before loading the file.
	 * @param string $method When $method is a string it contains a single file name.
	 * @param array $method a comma separated list of all the file names to be loaded.
	 * @return void
	 */
	public static function LoadMethod($method)
	{
		global $sourcedir;

		if (empty($method))
			return;

		if (is_array($method) && !empty($method))
				foreach($method as $m)
						require_once($sourcedir.'/Breeze/Breeze_'.$m.'.php');

		else
			require_once($sourcedir.'/Breeze/Breeze_'.$method.'.php');
	}

	/**
	 * Global permissions used by this mod per user group
	 *
	 * @param array $permissionGroups An array containing all possible permissions groups.
	 * @param array $permissionList An associative array with all the possible permissions.
	 * @return void
	 */
	public static function Permissions(&$permissionGroups, &$permissionList)
	{
		$permissionGroups['membergroup']['simple'] = array('breeze_per');
		$permissionGroups['membergroup']['classic'] = array('breeze_per');
		$permissionList['membergroup']['breeze_view_general_wall'] = array(true, 'breeze_per', 'breeze_per');
		$permissionList['membergroup']['breeze_edit_general_settings'] = array(true, 'breeze_per', 'breeze_per');
		$permissionList['membergroup']['breeze_delete_entries_own_wall'] = array(true, 'breeze_per', 'breeze_per');
		$permissionList['membergroup']['breeze_delete_entries_any_wall'] = array(false, 'breeze_per', 'breeze_per');
		$permissionList['membergroup']['breeze_delete_comments_own_wall'] = array(true, 'breeze_per', 'breeze_per');
		$permissionList['membergroup']['breeze_delete_comments_own_wall'] = array(false, 'breeze_per', 'breeze_per');
		$permissionList['membergroup']['breeze_delete_entries_made_by_me'] = array(false, 'breeze_per', 'breeze_per');
		$permissionList['membergroup']['breeze_delete_comments_made_by_me'] = array(false, 'breeze_per', 'breeze_per');
		$permissionList['membergroup']['breeze_edit_settings_any'] = array(false, 'breeze_per', 'breeze_per');
	}

	/**
	 * Replace the summary action with the ction created by Breeze
	 *
	 * @see Breeze_User::Wall()
	 * @param array $profile_areas An array containing all possible tabs for the profile menu.
	 * @return void
	 */
	public static function Profile_Info(&$profile_areas)
	{
		global $txt;

		loadLanguage('Breeze');
		Breeze::LoadMethod('Settings');

		/* Settings are required here */
		$s = Breeze_Settings::getInstance();

		/* Replace the summary page only if the mod is enable */
		if ($s->enable('breeze_admin_settings_enable'))
			$profile_areas['info']['areas']['summary'] = array(
				'label' => $txt['breeze_general_wall'],
				'file' => '/Breeze/Breeze_User.php',
				'function' => 'Breeze_Wrapper_Wall',
				'permission' => array(
					'own' => 'profile_view_own',
					'any' => 'profile_view_any',
				),
			);

		 /* Per user permissions */
		if ($s->enable('breeze_admin_settings_enable'))
			$profile_areas['breeze_profile'] = array(
				'title' => $txt['breeze_general_my_wall_settings'],
				'areas' => array(),
			);

		/* User individual settings goes right here... */
		$profile_areas['breeze_profile']['areas']['breezesettings'] = array(
			'label' => $txt['breeze_user_settings_name'],
			'file' => 'Breeze/Breeze_User.php',
			'function' => 'Breeze_Wrapper_Settings',
			'permission' => array(
				'own' => 'profile_view_own',
				'any' => 'profile_view_any',
				),
		);

		$profile_areas['breeze_profile']['areas']['breezepermissions'] = array(
			'label' => $txt['breeze_user_permissions_name'],
			'file' => 'Breeze/Breeze_User.php',
			'function' => 'Breeze_Wrapper_Permissions',
			'permission' => array(
				'own' => 'profile_view_own',
				'any' => 'breeze_edit_settings_any',
			),
		);
		/* Done with the hacking... */
	}

	/**
	 * Insert a Wall button on the menu buttons array
	 *
	 * @param array $menu_buttons An array containing all possible tabs for the main menu.
	 * @link http://mattzuba.com
	 * @return void
	 */
	public static function Wall_Menu(&$menu_buttons){

		global $scripturl, $txt, $context;

		loadLanguage('Breeze');
		Breeze::LoadMethod('Settings');

		/* Settings are required here */
		$s = Breeze_Settings::getInstance();

		/* Does the General Wall is enable? */
		if ($s->enable('breeze_admin_settings_enablegeneralwall') == false)
			return;

		$insert = $s->get('breeze_admin_settings_menuposition') == 'home' ? 'home' : $s->get('breeze_admin_settings_menuposition');

		/* Let's add our button next to the admin's selection...
		Thanks to SlammedDime <http://mattzuba.com> for the example */
		$counter = 0;
		foreach ($menu_buttons as $area => $dummy)
			if (++$counter && $area == $insert)
				break;

		$menu_buttons = array_merge(
			array_slice($menu_buttons, 0, $counter),
			array('breeze_Wall' => array(
			'title' => $txt['breeze_general_wall'],
			'href' => $scripturl . '?action=wall',
			'show' => allowedTo('breeze_view_general_wall'),
			'sub_buttons' => array(
				'my_wall' => array(
					'title' => $txt['breeze_general_my_wall'],
					'href' => $scripturl . '?action=profile',
					'show' => allowedTo('profile_view_own'),
					'sub_buttons' => array(
						'my_wall_settings' => array(
							'title' => $txt['breeze_user_settings_name'],
							'href' => $scripturl . '?action=profile;area=breezesettings',
							'show' => allowedTo('profile_view_own'),
						),
						'my_wall_permissions' => array(
							'title' => $txt['breeze_user_permissions_name'],
							'href' => $scripturl . '?action=profile;area=breezepermissions',
							'show' => allowedTo('profile_view_own'),
						),
					),
				),
				'breeze_admin_panel' => array(
					'title' => $txt['breeze_admin_settings_admin_panel'],
					'href' => $scripturl . '?action=admin;area=breezeindex',
					'show' => allowedTo('breeze_edit_general_settings'),
				),
			),
		)),
			array_slice($menu_buttons, $counter)
		);
	}

	/**
	 * Insert the actions needed by this mod
	 *
	 * @param array $actions An array containing all possible SMF actions.
	 * @return void
	 */
	public static function Action_Hook(&$actions)
	{
		$actions['wall'] = array('/Breeze/Breeze_General.php', 'Breeze_General::Wall');

		/* A whole new action just for some ajax calls... */
		$actions['breezeajax'] = array('/Breeze/Breeze_Ajax.php', 'Breeze_Ajax::factory');
	}

	/**
	 * DUH! WINNING!
	 *
	 * Used in the credits action
	 * @return a link for copyright notice
	 */
	public static function Who()
	{
		$MAS = '<a href="http://missallsunday.com" title="Free SMF Mods">Breeze mod &copy Suki</a>';

		return $MAS;
	}

	/* It's all about Admin settings from now on */
	public static function Admin_Button(&$admin_menu)
	{
		global $txt;

		loadLanguage('Breeze');

		$admin_menu['breezeadmin']= array(
			'title' => $txt['breeze_admin_settings_admin_panel'],
			'permission' => array('breeze_edit_general_settings'),
			'areas' => array(
				'breezeindex' => array(
					'label' => $txt['breeze_admin_settings_main'],
					'file' => 'Breeze/Breeze_Admin.php',
					'function' => 'Breeze_Admin_Main',
					'icon' => 'administration.gif',
					'permission' => array('breeze_edit_general_settings'),
				),
				'breezesettings' => array(
					'label' => $txt['breeze_admin_settings_settings'],
					'file' => 'Breeze/Breeze_Admin.php',
					'function' => 'Breeze_Admin_Settings',
					'icon' => 'corefeatures.gif',
					'permission' => array('breeze_edit_general_settings'),
				),
				'breezedonate' => array(
					'label' => $txt['breeze_admin_settings_donate'],
					'file' => 'Breeze/Breeze_Admin.php',
					'function' => 'Breeze_Admin_Donate',
					'icon' => 'support.gif',
					'permission' => array('breeze_edit_general_settings'),
				),
			),
		);
	}
}

	/* And so it is
	 Just like you said it would be
	 We'll both forget the breeze
	 Most of the time
	 And so it is
	 The shorter story
	 No love, no glory
	 No hero in her skies */