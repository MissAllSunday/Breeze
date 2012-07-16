<?php

/**
 * Breeze
 *
 * The purpose of this file is, the main file, handles the hooks, the actions, permissions, load needed files, etc.
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

/* Autoload */
function __autoload($class_name)
{
	global $sourcedir;

	$file_path = $sourcedir.Breeze::$BreezeFolder.$class_name . '.php';

	if(file_exists($file_path))
		require_once($file_path);

	else
		return false;
}

class Breeze
{
	static public $BreezeName = 'Breeze';
	static public $BreezeVersion = '1.0 Beta 3';
	static public $BreezeFolder = '/Breeze/';

	public function __construct(){}

	/**
	 * Load SMF source files
	 *
	 * @param string $file When $file is a string it contains a single file name.
	 * @param array $file a comma separated list of all the file names to be loaded.
	 * @return void
	 */
	public static function load($file)
	{
		global $sourcedir;

		if (empty($file))
			return;

		if (is_array($file) && !empty($file))
				foreach($file as $f)
					require_once($sourcedir. '/'.$f.'.php');

		elseif (!empty($file))
			require_once($sourcedir .'/'.$file.'.php');
	}

	public function userSettings($user)
	{
		return new BreezeUserSettings($user);
	}

	public function query()
	{
		return BreezeQuery::getInstance();
	}

	public function quickQuery($table)
	{
		return new BreezeQuery($table);
	}

	public function tools()
	{
		return new BreezeSubs();
	}

	public function settings()
	{
		return BreezeSettings::getInstance();
	}

	public function text()
	{
		return BreezeText::getInstance();
	}

	public function sGlobals($var)
	{
		return new BreezeGlobals($var);
	}

	public function parser()
	{
		return new BreezeParser();
	}

	public function notifications()
	{
		return new BreezeNotifications();
	}

	public function notificationStream()
	{
		global $user_info;

		/* Guest don't need to see this */
		if(empty($user_info['is_guest']))
		{
			$notifications = self::notifications();
			$notifications->doStream($user_info['id']);
		}
	}

	/**
	 * Global permissions used by this mod per user group
	 *
	 * There is only permissions to post new status and comments on any profile because people needs to be able to post in their own profiles by default te same goes for deleting, people are able to delete their own status/comments on their own profile page.
	 * @param array $permissionGroups An array containing all possible permissions groups.
	 * @param array $permissionList An associative array with all the possible permissions.
	 * @return void
	 */
	public static function permissions(&$permissionGroups, &$permissionList)
	{
		$permissionList['membergroup']['breeze_edit_settings_any'] = array(false, 'breeze_per_classic', 'breeze_per_simple');
		$permissionGroups['membergroup']['simple'] = array('breeze_per_simple');
		$permissionGroups['membergroup']['classic'] = array('breeze_per_classic');
		$permissionList['membergroup']['breeze_deleteStatus'] = array(false, 'breeze_per_classic', 'breeze_per_simple');
		$permissionList['membergroup']['breeze_postStatus'] = array(false, 'breeze_per_classic', 'breeze_per_simple');
		$permissionList['membergroup']['breeze_postComments'] = array(false, 'breeze_per_classic', 'breeze_per_simple');
	}

	/**
	 * Replace the summary action with the action created by Breeze
	 *
	 * @see BreezeUser::Wall()
	 * @param array $profile_areas An array containing all possible tabs for the profile menu.
	 * @return void
	 */
	public static function profileInfo(&$profile_areas)
	{
		global $user_info, $context;

		/* Settings are required here */
		$gSettings = self::settings();
		$text = self::text();

		/* Replace the summary page only if the mod is enable */
		if ($gSettings->enable('admin_settings_enable'))
			$profile_areas['info']['areas']['summary'] = array(
				'label' => $text->getText('general_wall'),
				'file' => Breeze::$BreezeFolder .'BreezeUser.php',
				'function' => 'Breeze_Wrapper_Wall',
				'permission' => array(
					'own' => 'profile_view_own',
					'any' => 'profile_view_any',
				),
			);

		/* If the mod is enable, then create another page for the default profile page */
		if ($gSettings->enable('admin_settings_enable'))
			$profile_areas['info']['areas']['static'] = array(
					'label' => $text->getText('general_summary'),
					'file' => 'Profile-View.php',
					'function' => 'summary',
					'permission' => array(
						'own' => 'profile_view_own',
						'any' => 'profile_view_any',
					),
				);

		/* Per user permissions */
		if ($gSettings->enable('admin_settings_enable'))
			$profile_areas['breeze_profile'] = array(
				'title' => $text->getText('general_my_wall_settings'),
				'areas' => array(),
			);

		/* User individual settings, show the button if the mod is enable and the user is the profile owner or the user has the permissions to edit other walls */
		if ($gSettings->enable('admin_settings_enable') && ($user_info['id'] == $context['member']['id'] || allowedTo('breeze_edit_settings_any')))
			$profile_areas['breeze_profile']['areas']['breezesettings'] = array(
				'label' => $text->getText('user_settings_name'),
				'file' => Breeze::$BreezeFolder .'BreezeUser.php',
				'function' => 'Breeze_Wrapper_Settings',
				'permission' => array(
					'own' => 'profile_view_own',
					'any' => 'breeze_edit_settings_any',
					),
			);

		/* Buddies page */
		/* if $some_check here */
			$profile_areas['breeze_profile']['areas']['breezebuddies'] = array(
				'label' => $text->getText('user_buddysettings_name'),
				'file' => Breeze::$BreezeFolder .'BreezeUser.php',
				'function' => 'Breeze_Wrapper_BuddyRequest',
				'permission' => array(
					'own' => 'profile_view_own',
					),
			);

		/* Notifications admin page */
		/* if $some_check here */
			$profile_areas['breeze_profile']['areas']['breezenoti'] = array(
				'label' => $text->getText('user_notisettings_name'),
				'file' => Breeze::$BreezeFolder .'BreezeUser.php',
				'function' => 'Breeze_Wrapper_Notifications',
				'permission' => array(
					'own' => 'profile_view_own',
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
	public static function wallMenu(&$menu_buttons)
	{
		global $scripturl, $context;

		/* Settings are required here */
		$gSettings = self::settings();
		$text = self::text();

		/* Does the General Wall is enable? */
		if ($gSettings->enable('admin_settings_enablegeneralwall') == false)
			return;

		$insert = $gSettings->getSetting('breeze_admin_settings_menuposition') == 'home' ? 'home' : $gSettings->getSetting('breeze_admin_settings_menuposition');

		/* Let's add our button next to the admin's selection...
		Thanks to SlammedDime <http://mattzuba.com> for the example */
		$counter = 0;
		foreach ($menu_buttons as $area => $dummy)
			if (++$counter && $area == $insert)
				break;

		$menu_buttons = array_merge(
			array_slice($menu_buttons, 0, $counter),
			array('breeze_Wall' => array(
				'title' => $text->getText('general_wall'),
				'href' => $scripturl . '?action=wall',
				'show' => allowedTo('breeze_view_general_wall'),
				'sub_buttons' => array(
					'my_wall' => array(
						'title' => $text->getText('general_my_wall'),
						'href' => $scripturl . '?action=profile',
						'show' => allowedTo('profile_view_own'),
						'sub_buttons' => array(
							'my_wall_settings' => array(
								'title' => $text->getText('user_settings_name'),
								'href' => $scripturl . '?action=profile;area=breezesettings',
								'show' => allowedTo('profile_view_own'),
							),
							'my_wall_permissions' => array(
								'title' => $text->getText('user_permissions_name'),
								'href' => $scripturl . '?action=profile;area=breezepermissions',
								'show' => allowedTo('profile_view_own'),
							),
						),
					),
					'breeze_admin_panel' => array(
						'title' => $text->getText('admin_settings_admin_panel'),
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
	public static function actions(&$actions)
	{

		/* A whole new action just for some ajax calls... */
		$actions['breezeajax'] = array(Breeze::$BreezeFolder .'BreezeAjax.php', 'BreezeAjax::call');

		/* The general wall */
		$actions['wall'] = array(Breeze::$BreezeFolder .'BreezeGeneral.php', 'BreezeGeneral::call');

		/* Replace the buddy action */
		$actions['buddy'] = array(Breeze::$BreezeFolder .'BreezeBuddy.php', 'BreezeBuddy::buddy');

		/* A special action for the buddy request message */
		$actions['breezebuddyrequest'] = array(Breeze::$BreezeFolder .'BreezeUser.php', 'Breeze_Wrapper_BuddyMessageSend');
	}

	/**
	 * DUH! WINNING!
	 *
	 * Used in the credits action
	 * @return string a link for copyright notice
	 */
	public static function who()
	{
		return '<a href="http://missallsunday.com" title="Free SMF Mods">Breeze mod &copy Suki</a>';
	}

	/* It's all about Admin settings from now on */
	public static function AdminButton(&$admin_menu)
	{
		$text = Breeze::text();

		$admin_menu['breezeadmin'] = array(
			'title' => $text->getText('admin_settings_admin_panel'),
			'permission' => array('breeze_edit_general_settings'),
			'areas' => array(
				'breezeindex' => array(
					'label' => $text->getText('admin_settings_main'),
					'file' => 'Breeze/BreezeAdmin.php',
					'function' => 'Breeze_Admin_Main',
					'icon' => 'administration.gif',
					'permission' => array('breeze_edit_general_settings'),
				),
				'breezesettings' => array(
					'label' => $text->getText('admin_settings_settings'),
					'file' => 'Breeze/BreezeAdmin.php',
					'function' => 'Breeze_Admin_Settings',
					'icon' => 'corefeatures.gif',
					'permission' => array('breeze_edit_general_settings'),
				),
				'breezedonate' => array(
					'label' => $text->getText('admin_settings_donate'),
					'file' => 'Breeze/BreezeAdmin.php',
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