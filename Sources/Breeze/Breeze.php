<?php

/**
 * Breeze
 *
 * The purpose of this file is, the main file, handles the hooks, the actions, permissions, load needed files, etc.
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2013 Jessica González
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
	die('No direct access...');

/**
 * breeze_autoloader()
 *
 * @param mixed $class_name
 * @return
 */
function breeze_autoloader($class_name)
{
	global $sourcedir;

	$file_path = $sourcedir . Breeze::$folder . $class_name . '.php';

	if (file_exists($file_path))
		require_once ($file_path);

	else
		return false;
}

spl_autoload_register('breeze_autoloader');

class Breeze
{
	public static $name = 'Breeze';
	public static $version = '1.0 Beta 3';
	public static $folder = '/Breeze/';
	public static $txtpattern = 'Breeze_';

	/* Support site feed */
	public static $supportStite = 'http://missallsunday.com/index.php?action=.xml;sa=news;board=11;limit=10;type=rss2';

	/* Its easier to list the allowed actions */
	public static $_allowedActions = array('display', 'unread', 'unreadreplies', 'viewprofile', 'profile', 'who',);

	/**
	 * Breeze::__construct()
	 *
	 * @return
	 */
	public function __construct(){}

	/**
	 * Breeze::load()
	 *
	 * @param string $file When $file is a string it contains a single file name.
	 * @param array $file a comma separated list of all the file names to be loaded.
	 * @return
	 */
	public static function load($file)
	{
		global $sourcedir;

		if (empty($file))
			return;

		if (is_array($file) && !empty($file))
			foreach ($file as $f)
				require_once ($sourcedir . '/' . $f . '.php');

		elseif (!empty($file))
			require_once ($sourcedir . '/' . $file . '.php');
	}

	/**
	 * Breeze::instantiate()
	 *
	 *@param string The name of the class
	 * @return object Access to the class
	 */
	public function instantiate($objectName, $param = false)
	{
		if (empty($objectName))
			return false;

		$objectName = ucfirst($objectName);
		$class = self::$name . $objectName;
		return new $class($param ? $param : null);
	}

	/**
	 * Breeze::sGlobals()
	 *
	 * @param string $var Either post, request or get
	 * @return object Acces to BreezeGlobals
	 */
	public function sGlobals($var)
	{
		return new BreezeGlobals($var);
	}

	/**
	 * Breeze::headersHook()
	 *
	 * Static method used to embed the JavaScript and other bits of code on every page inside SMF, used by the SMF hook system
	 * @see BreezeTools
	 * @return void
	 */
	public static function headersHook($type = 'noti')
	{
		global $context, $settings, $user_info, $breezeController;
		static $header_done = false;

		if (empty($breezeController))
			$breezeController = new BreezeController();

		$text = $breezeController->get('text');
		$breezeSettings = $breezeController->get('settings');
		$breezeGlobals = Breeze::sGlobals('get');

		if (!$header_done)
		{
			$context['html_headers'] .= '
			<script type="text/javascript">!window.jQuery && document.write(unescape(\'%3Cscript src="http://code.jquery.com/jquery.min.js"%3E%3C/script%3E\'))</script>
			<link href="'. $settings['default_theme_url'] .'/css/breeze.css" rel="stylesheet" type="text/css" />';

			/* DUH! winning! */
			if ($breezeGlobals->getValue('action') == 'profile' && $breezeSettings->enable('admin_settings_enable'))
				$context['insert_after_template'] .= Breeze::who();

			$header_done = true;
		}

		/* Define some variables for the ajax stuff */
		if ($type == 'profile')
		{
			$context['html_headers'] .= '
			<script type="text/javascript"><!-- // --><![CDATA[
				var breeze_error_message = "'. $text->getText('error_message') .'";
				var breeze_success_message = "'. $text->getText('success_message') .'";
				var breeze_empty_message = "'. $text->getText('empty_message') .'";
				var breeze_error_delete = "'. $text->getText('error_message') .'";
				var breeze_success_delete = "'. $text->getText('success_delete') .'";
				var breeze_confirm_delete = "'. $text->getText('confirm_delete') .'";
				var breeze_confirm_yes = "'. $text->getText('confirm_yes') .'";
				var breeze_confirm_cancel = "'. $text->getText('confirm_cancel') .'";
				var breeze_already_deleted = "'. $text->getText('already_deleted') .'";
				var breeze_cannot_postStatus = "'. $text->getText('cannot_postStatus') .'";
				var breeze_cannot_postComments = "'. $text->getText('cannot_postComments') .'";
				var breeze_page_loading = "'. $text->getText('page_loading') .'";
				var breeze_page_loading_end = "'. $text->getText('page_loading_end') .'";
		// ]]></script>';

			/* Let's load jquery from CDN only if it hasn't been loaded yet */
			$context['html_headers'] .= '
			<link href="'. $settings['default_theme_url'] .'/css/facebox.css" rel="stylesheet" type="text/css" />
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/facebox.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/livequery.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/jquery.noty.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/top.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/center.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/topCenter.js"></script>';

			/* Does the user wants to use infinite scroll? */
			if (!empty($context['member']['options']['Breeze_infinite_scroll']))
				$context['html_headers'] .= '
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.infinitescroll.min.js" type="text/javascript"></script>';

			/* Load breeze.js untill everyone else is loaded */
			$context['html_headers'] .= '
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breeze.js"></script>';
		}

		/* Does the admin wants to add more actions? */
		if ($breezeSettings->enable('allowedActions'))
			Breeze::$_allowedActions = array_merge(Breeze::$_allowedActions, explode(',', $breezeSettings->getSetting('allowedActions')));

		/* Stuff for the notifications, don't show this if we aren't on a specified action */
		if ($type == 'noti' && empty($user_info['is_guest']) && (in_array($breezeGlobals->getValue('action'), Breeze::$_allowedActions) || $breezeGlobals->getValue('action') == false))
		{
			$notifications = $breezeController->get('notifications');

			$context['insert_after_template'] .= '
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/jquery.noty.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/top.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/topLeft.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/topRight.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/themes/default.js"></script>
			<script type="text/javascript"><!-- // --><![CDATA[
				var breeze_error_message = "'. $text->getText('error_message') .'";
				var breeze_noti_markasread = "'. $text->getText('noti_markasread') .'";
				var breeze_noti_markasread_after = "'. $text->getText('noti_markasread_after') .'";
				var breeze_noti_delete = "'. $text->getText('general_delete') .'";
				var breeze_noti_delete_after = "'. $text->getText('noti_delete_after') .'";
				var breeze_noti_close = "'. $text->getText('noti_close') .'";
				var breeze_noti_cancel = "'. $text->getText('confirm_cancel') .'";
			// ]]></script>';

			$context['insert_after_template'] .= $notifications->doStream($user_info['id']);
		}

		/* Admin bits */
		if($type == 'admin')
			$context['html_headers'] .= '
			<script src="'. $settings['default_theme_url'] .'/js/jquery.zrssfeed.js" type="text/javascript"></script>
			<script type="text/javascript">
var breeze_feed_error_message = "'. $text->getText('feed_error_message') .'";

$(document).ready(function ()
{
	$(\'#breezelive\').rssfeed(\''. Breeze::$supportStite .'\',
	{
		limit: 5,
		header: false,
		date: true,
		linktarget: \'_blank\',
		errormsg: breeze_feed_error_message
   });
});
 </script>
';

	}

	/**
	 * Breeze::permissions()
	 *
	 * There is only permissions to post new status and comments on any profile because people needs to be able to post in their own profiles by default the same goes for deleting, people are able to delete their own status/comments on their own profile page.
	 * @param array $permissionGroups An array containing all possible permissions groups.
	 * @param array $permissionList An associative array with all the possible permissions.
	 * @return void
	 */
	public static function permissions(&$permissionGroups, &$permissionList)
	{
		$permissionList['membergroup']['breeze_edit_settings_any'] = array(
			false,
			'breeze_per_classic',
			'breeze_per_simple');
		$permissionGroups['membergroup']['simple'] = array('breeze_per_simple');
		$permissionGroups['membergroup']['classic'] = array('breeze_per_classic');
		$permissionList['membergroup']['breeze_deleteStatus'] = array(
			false,
			'breeze_per_classic',
			'breeze_per_simple');
		$permissionList['membergroup']['breeze_postStatus'] = array(
			false,
			'breeze_per_classic',
			'breeze_per_simple');
		$permissionList['membergroup']['breeze_postComments'] = array(
			false,
			'breeze_per_classic',
			'breeze_per_simple');
	}

	/**
	 * Breeze::profile()
	 *
	 * Replace the summary action with the action created by Breeze
	 *
	 * @see BreezeUser::wall()
	 * @param array $profile_areas An array containing all possible tabs for the profile menu.
	 * @return void
	 */
	public static function profile(&$profile_areas)
	{
		global $user_info, $context, $breezeController;

		if (empty($breezeController))
			$breezeController = new BreezeController();

		/* Settings are required here */
		$gSettings = $breezeController->get('settings');
		$text = $breezeController->get('text');

		/* Replace the summary page only if the mod is enable */
		if ($gSettings->enable('admin_settings_enable'))
			$profile_areas['info']['areas']['summary'] = array(
				'label' => $text->getText('general_wall'),
				'file' => Breeze::$folder . 'BreezeUser.php',
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
		if ($gSettings->enable('admin_settings_enable'))
			$profile_areas['breeze_profile']['areas']['breezesettings'] = array(
				'label' => $text->getText('user_settings_name'),
				'file' => Breeze::$folder . 'BreezeUser.php',
				'function' => 'Breeze_Wrapper_Settings',
				'sc' => 'post',
				'permission' => array(
					'own' => array(
						'profile_view_own',
						'profile_view_any',
						),
					'any' => array('profile_view_any'),
					),
				);

		/* Buddies page */
		/* if $some_check here */
		$profile_areas['breeze_profile']['areas']['breezebuddies'] = array(
			'label' => $text->getText('user_buddysettings_name'),
			'file' => Breeze::$folder . 'BreezeUser.php',
			'function' => 'Breeze_Wrapper_BuddyRequest',
			'permission' => array('own' => 'profile_view_own', ),
			);

		/* Notifications admin page */
		/* if $some_check here */
		$profile_areas['breeze_profile']['areas']['breezenoti'] = array(
			'label' => $text->getText('user_notisettings_name'),
			'file' => Breeze::$folder . 'BreezeUser.php',
			'function' => 'Breeze_Wrapper_Notifications',
			'permission' => array('own' => 'profile_view_own', ),
			);

		/* Done with the hacking... */
	}

	/**
	 * Breeze::menu()
	 *
	 * Insert a Wall button on the menu buttons array
	 * @param array $menu_buttons An array containing all possible tabs for the main menu.
	 * @link http://mattzuba.com
	 * @return void
	 */
	public static function menu(&$menu_buttons)
	{
		global $scripturl, $context, $breezeController;

		/* Settings are required here */
		$gSettings = $breezeController->get('settings');
		$text = $breezeController->get('text');

		/* Does the General Wall is enable? */
		if ($gSettings->enable('admin_settings_enablegeneralwall') == false)
			return;

		$insert = $gSettings->getSetting('breeze_admin_settings_menuposition') == 'home' ?
			'home':$gSettings->getSetting('breeze_admin_settings_menuposition');

		/* Let's add our button next to the admin's selection...
		* Thanks to SlammedDime <http://mattzuba.com> for the example */
		$counter = 0;
		foreach ($menu_buttons as $area => $dummy)
			if (++$counter && $area == $insert)
				break;

		$menu_buttons = array_merge(array_slice($menu_buttons, 0, $counter), array('breeze_Wall' =>
				array(
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
				)), array_slice($menu_buttons, $counter));
	}

	/**
	 * Breeze::actions()
	 *
	 * Insert the actions needed by this mod
	 * @param array $actions An array containing all possible SMF actions.
	 * @return void
	 */
	public static function actions(&$actions)
	{
		/* A whole new action just for some ajax calls... */
		$actions['breezeajax'] = array(Breeze::$folder . 'BreezeDispatcher.php', 'BreezeDispatcher::dispatch');

		/* The general wall */
		$actions['wall'] = array(Breeze::$folder . 'BreezeDispatcher.php', 'BreezeDispatcher::dispatch');

		/* Replace the buddy action */
		$actions['buddy'] = array(Breeze::$folder . 'BreezeDispatcher.php', 'BreezeDispatcher::dispatch');

		/* A special action for the buddy request message */
		$actions['breezebuddyrequest'] = array(Breeze::$folder . 'BreezeUser.php', 'BreezeUser::buddyMessageSend');
	}

	/**
	 * Breeze::who()
	 *
	 * Used in the credits action
	 * @return string a link for copyright notice
	 */
	public static function who()
	{
		return '<div style="margin:auto; text-align:center"><a href="http://missallsunday.com" title="Free SMF Mods">Breeze mod &copy Suki</a></div>';
	}

	/* It's all about Admin settings from now on */

	/**
	 * Breeze::admin()
	 *
	 * @param array $admin_menu An array with all the admin settings buttons
	 * @return
	 */
	public static function admin(&$admin_menu)
	{
		global $breezeController;

		$text = $breezeController->get('text');

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
* Just like you said it would be
* We'll both forget the breeze
* Most of the time
* And so it is
* The shorter story
* No love, no glory
* No hero in her skies */
