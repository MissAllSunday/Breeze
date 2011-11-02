<?php

/**
 * @package breeze mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2011 Suki
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */

	/* If you want to alter, transform, or build upon this work and release the result, please leave the original copyright statements and add yours below, thanks. */

if (!defined('SMF'))
	die('Hacking attempt...');

	/* The needed class */
	function __autoload($classname)
	{
		global $sourcedir;

		include($sourcedir.'/Breeze/'.$classname.'.php');
	}

class Breeze
{
	/* Load all settings from the database */
	public function __construct()
	{

		$this->breeze['global_settings'] = array();

		$params = array(
				'rows' => 'enable, menu_position, enable_general_wall',
		);

		$query = new BreezeDB('breeze_Settings');
		$query->Params($params);
		$query->GetData();

		if (!empty($query->data_result))
			$this->breeze['global_settings'] = $query->data_result;

		/* Define all settings as empty, me wantz no problems */
		else
			$this->breeze['global_settings'] = array(
				'enable' => '',
				'menu_position' => '',
				'enable_general_wall' => ''
			);
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

		if (!empty($this->breeze['global_settings']['enable']))
			$profile_areas['info']['summary'] = array(
				'label' => $txt['breeze_general_wall'],
				'file' => 'Breeze_User.php',
				'function' => 'Breeze_User::Wall',
				'permission' => array(
					'own' => 'profile_view_own',
					'any' => 'profile_view_any',
				),
			);

		/* User individual settings goes right here... */
		$profile_areas['breeze_profile'] = array(
			'CMKYsettings' => array(
				'label' => $txt['breeze_user_settings_name'],
				'file' => 'Breeze_User.php',
				'function' => 'Breeze_User::Settings',
				'permission' => array(
					'own' => 'profile_view_own',
					'any' => 'profile_view_any',
					),
				),
			'CMKYpermissions' => array(
				'label' => $txt['breeze_user_permissions_name',
				'file' => 'Breeze_User.php',
				'function' => 'Breeze_User::Permissions',
				'permission' => array(
					'own' => 'profile_view_own',
					'any' => 'breeze_edit_settings_any',
				),
			),
		);
		/* Done with the hacking... */
	}

	/* The almighty Wall button */
	public static function Wall_Menu(&$menu_buttons){

		global $scripturl, $txt;

		loadLanguage('Breeze');

		$insert = empty($this->breeze['global_settings']['menu_position']) ? 'home' : $this->breeze['global_settings']['menu_position'];

		/* Does the General Wall is enable? */
		if (empty($this->breeze['global_settings']['enable_general_wall']))
			return;

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
							'href' => $scripturl . '?action=profile;area=CMKYsettings',
							'show' => allowedTo('profile_view_own'),
						),
						'my_wall_permissions' => array(
							'title' => $txt['breeze_user_permissions_name',
							'href' => $scripturl . '?action=profile;area=CMKYpermissions',
							'show' => allowedTo('profile_view_own'),
						),
					),
				),
				'breeze_admin_panel' => array(
					'title' => $txt['breeze_admin_settings_admin_panel'],
					'href' => $scripturl . '?action=admin;area=breezeadmin',
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
		$actions['wall'] = array('Breeze_General.php', 'Breeze_General::Wall');
	}

	/* It's all about Admin settings from now on */
	public static function Admin_Button(&$admin_areas){

		global $txt, $context, $sourcedir;

		loadLanguage('Breeze');

		$admin_areas['breezeadmin']= array(
			'title' => $txt['breeze_admin_settings_admin_panel'],
			'permission' => array('breeze_edit_general_settings'),
			'areas' => array(
				'breezeindex' => array(
					'label' => $txt['breeze_admin_settings_main'],
					'file' => 'Breeze_Admin',
					'function' => 'Breeze_Admin::Main',
					'icon' => 'administration.gif',
					'permission' => array('breeze_edit_general_settings'),
				),
				'breezesettings' => array(
					'label' => $txt['breeze_admin_settings_settings'],
					'file' => 'Breeze_Admin.php',
					'function' => 'Breeze_Admin::Settings',
					'icon' => 'corefeatures.gif',
					'permission' => array('breeze_edit_general_settings'),
				),
				'breezedonate' => array(
					'label' => $txt['breeze_admin_settings_donate'],
					'file' => 'Breeze_Admin.php',
					'function' => 'Breeze_Admin::Donate',
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
?>
