<?php

/**
 * @package breeze mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2011 Suki
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */

	/* If you want to alter, transform, or build upon this work and release the result, please leave the original copyright statements and add yours above, thanks. */

if (!defined('SMF'))
	die('Hacking attempt...');

class Breeze
{
	public function __construct()
	{
	}

	/* An attempt to load the static method(s) used across the mod */
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

	/* Who can use this stuff? */
	public static function Permissions(&$permissionGroups, &$permissionList){

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

	/* Let's hack the profile page... not really, we will just replace the existing page with our own.*/
	public static function Profile_Info(&$profile_areas)
	{
		global $txt;

		loadLanguage('Breeze');
		Breeze::LoadMethod('Settings');

		/* Settings are required here */
		$s = Breeze_Settings::getInstance();

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

		if ($s->enable('breeze_admin_settings_enable'))
			$profile_areas['breeze_profile'] = array(
				'title' => $txt['breeze_general_my_wall_settings'],
				'areas' => array(),
			);

		/* User individual settings goes right here... */
		$profile_areas['breeze_profile']['areas']['breezesettings'] = array(
			'label' => $txt['breeze_user_settings_name'],
			'file' => 'Breeze_User.php',
			'function' => 'Breeze_Wrapper_Settings',
			'permission' => array(
				'own' => 'profile_view_own',
				'any' => 'profile_view_any',
				),
		);

		$profile_areas['breeze_profile']['areas']['breezepermissions'] = array(
			'label' => $txt['breeze_user_permissions_name'],
			'file' => 'Breeze_User.php',
			'function' => 'Breeze_Wrapper_Permissions',
			'permission' => array(
				'own' => 'profile_view_own',
				'any' => 'breeze_edit_settings_any',
			),
		);
		/* Done with the hacking... */
	}

	/* The almighty Wall button */
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

	/* Set the action for the General Wall */
	public static function Action_Hook(&$actions)
	{
		$actions['wall'] = array('/Breeze/Breeze_General.php', 'Breeze_General::Wall');

		/* A whole new action just for some ajax calls... */
		$actions['breezeajax'] = array('/Breeze/Breeze_Ajax.php', 'Breeze_Ajax::factory');
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

