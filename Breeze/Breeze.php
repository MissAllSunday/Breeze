<?php

/**
 * Breeze
 *
 * The purpose of this file is, the main file, handles the hooks, the actions, permissions, load needed files, etc.
 * @package Breeze mod
 * @version 1.0 Beta 2
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

class Breeze
{
	static public $breezeVersion = '1.0 Beta 1';

	static public $breezeFolder = '/Breeze/';

	public function __construct()
	{
	}

	/**
	 * An attempt to load the static method(s) used across the mod
	 *
	 * @todo Implement some checks before loading the file.
	 * @param string $file When $file is a string it contains a single file name.
	 * @param array $file a comma separated list of all the file names to be loaded.
	 * @param bool $smf special case used when trying to load an SMF source file for some resource or function
	 * @return void
	 */
	public static function loadFile($file, $smf = false)
	{
		global $sourcedir;

		if (empty($file))
			return;

		if (is_array($file) && !empty($file) && !$smf)
				foreach($file as $f)
					require_once($sourcedir. Breeze::$breezeFolder . 'Breeze'.$f.'.php');

		elseif (!$smf)
			require_once($sourcedir . Breeze::$breezeFolder .'Breeze'.$file.'.php');

		else
			require_once($sourcedir .'/'.$file.'.php');
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
		Breeze::loadFile('Settings');

		/* Settings are required here */
		$s = BreezeSettings::getInstance();

		/* Replace the summary page only if the mod is enable */
		if ($s->enableSetting('admin_settings_enable'))
			$profile_areas['info']['areas']['summary'] = array(
				'label' => $s->getText('general_wall'),
				'file' => Breeze::$breezeFolder .'BreezeUser.php',
				'function' => 'Breeze_Wrapper_Wall',
				'permission' => array(
					'own' => 'profile_view_own',
					'any' => 'profile_view_any',
				),
			);

		/* If the mod is enable, then create another page for the default profile page */
		if ($s->enableSetting('admin_settings_enable'))
			$profile_areas['info']['areas']['static'] = array(
					'label' => $s->getText('general_summary'),
					'file' => 'Profile-View.php',
					'function' => 'summary',
					'permission' => array(
						'own' => 'profile_view_own',
						'any' => 'profile_view_any',
					),
				);

		/* Per user permissions */
		if ($s->enableSetting('admin_settings_enable'))
			$profile_areas['breeze_profile'] = array(
				'title' => $s->getText('general_my_wall_settings'),
				'areas' => array(),
			);

		/* User individual settings, show the button if the mod is enable and the user is the profile owner or the user has the permissions to edit other walls */
		if ($s->enableSetting('admin_settings_enable') && ($user_info['id'] == $context['member']['id'] || allowedTo('breeze_edit_settings_any')))
			$profile_areas['breeze_profile']['areas']['breezesettings'] = array(
				'label' => $s->getText('user_settings_name'),
				'file' => Breeze::$breezeFolder .'BreezeUser.php',
				'function' => 'Breeze_Wrapper_Settings',
				'permission' => array(
					'own' => 'profile_view_own',
					'any' => 'breeze_edit_settings_any',
					),
			);

		/* Buddies page */
		/* if $some_check here */
			$profile_areas['breeze_profile']['areas']['breezebuddies'] = array(
				'label' => $s->getText('user_buddysettings_name'),
				'file' => Breeze::$breezeFolder .'BreezeUser.php',
				'function' => 'Breeze_Wrapper_BuddyRequest',
				'permission' => array(
					'own' => 'profile_view_own',
					),
			);

		/* Notifications admin page */
		/* if $some_check here */
			$profile_areas['breeze_profile']['areas']['breezenoti'] = array(
				'label' => $s->getText('user_notisettings_name'),
				'file' => Breeze::$breezeFolder .'BreezeUser.php',
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
	public static function WallMenu(&$menu_buttons)
	{
		global $scripturl, $context;

		loadLanguage('Breeze');
		Breeze::loadFile('Settings');

		/* Settings are required here */
		$s = BreezeSettings::getInstance();

		/* Does the General Wall is enable? */
		if ($s->enableSetting('admin_settings_enablegeneralwall') == false)
			return;

		$insert = $s->getSetting('breeze_admin_settings_menuposition') == 'home' ? 'home' : $s->getSetting('breeze_admin_settings_menuposition');

		/* Let's add our button next to the admin's selection...
		Thanks to SlammedDime <http://mattzuba.com> for the example */
		$counter = 0;
		foreach ($menu_buttons as $area => $dummy)
			if (++$counter && $area == $insert)
				break;

		$menu_buttons = array_merge(
			array_slice($menu_buttons, 0, $counter),
			array('breeze_Wall' => array(
				'title' => $s->getText('general_wall'),
				'href' => $scripturl . '?action=wall',
				'show' => allowedTo('breeze_view_general_wall'),
				'sub_buttons' => array(
					'my_wall' => array(
						'title' => $s->getText('general_my_wall'),
						'href' => $scripturl . '?action=profile',
						'show' => allowedTo('profile_view_own'),
						'sub_buttons' => array(
							'my_wall_settings' => array(
								'title' => $s->getText('user_settings_name'),
								'href' => $scripturl . '?action=profile;area=breezesettings',
								'show' => allowedTo('profile_view_own'),
							),
							'my_wall_permissions' => array(
								'title' => $s->getText('user_permissions_name'),
								'href' => $scripturl . '?action=profile;area=breezepermissions',
								'show' => allowedTo('profile_view_own'),
							),
						),
					),
					'breeze_admin_panel' => array(
						'title' => $s->getText('admin_settings_admin_panel'),
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
	public static function actionHook(&$actions)
	{
		$actions['wall'] = array(Breeze::$breezeFolder .'BreezeGeneral.php', 'BreezeGeneral::Wall');

		/* A whole new action just for some ajax calls... */
		$actions['breezeajax'] = array(Breeze::$breezeFolder .'BreezeAjax.php', 'BreezeAjax::call');

		/* Replace the buddy action */
		$actions['buddy'] = array(Breeze::$breezeFolder .'BreezeBuddy.php', 'BreezeBuddy::buddy');

		/* A special action for the buddy request message */
		$actions['breezebuddyrequest'] = array(Breeze::$breezeFolder .'BreezeUser.php', 'Breeze_Wrapper_BuddyMessageSend');
	}

	/**
	 * DUH! WINNING!
	 *
	 * Used in the credits action
	 * @return string a link for copyright notice
	 */
	public static function who()
	{
		$MAS = '<a href="http://missallsunday.com" title="Free SMF Mods">Breeze mod &copy Suki</a>';

		return $MAS;
	}

	/* It's all about Admin settings from now on */
	public static function adminButton(&$admin_menu)
	{
		Breeze::loadFile(array('Settings'));

		$text = BreezeSettings::getInstance();

		$admin_menu['breezeadmin'] = array(
			'title' => $text->getText('admin_settings_admin_panel'),
			'permission' => array('breeze_edit_general_settings'),
			'areas' => array(
				'breezeindex' => array(
					'label' => $text->getText('admin_settings_main'),
					'file' => 'Breeze/Breeze_Admin.php',
					'function' => 'breeze_admin_main',
					'icon' => 'administration.gif',
					'permission' => array('breeze_edit_general_settings'),
				),
				'breezesettings' => array(
					'label' => $text->getText('admin_settings_settings'),
					'file' => 'Breeze/Breeze_Admin.php',
					'function' => 'breeze_admin_settings',
					'icon' => 'corefeatures.gif',
					'permission' => array('breeze_edit_general_settings'),
				),
				'breezedonate' => array(
					'label' => $text->getText('admin_settings_donate'),
					'file' => 'Breeze/Breeze_Admin.php',
					'function' => 'breeze_admin_donate',
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