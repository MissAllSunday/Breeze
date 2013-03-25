<?php

/**
 * Breeze
 *
 * The purpose of this file is, the main file, handles the hooks, the actions, permissions, load needed files, etc.
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica Gonz�lez <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2013 Jessica Gonz�lez
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
* Jessica Gonz�lez.
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

	// Support site feed
	public static $supportStite = 'http://missallsunday.com/index.php?action=.xml;sa=news;board=11;limit=10;type=rss2';

	// Its easier to list the allowed actions
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
	public static function sGlobals($var)
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
		global $context, $settings, $user_info, $breezeController, $txt;
		static $header_done = false;

		// Don't do anything if we are in SSI world
		if (SMF == 'SSI')
			return false;

		if (empty($breezeController))
			$breezeController = new BreezeController();

		$text = $breezeController->get('text');
		$breezeSettings = $breezeController->get('settings');
		$breezeGlobals = Breeze::sGlobals('get');

		if (!$header_done)
		{
			$context['html_headers'] .= '
			<script type="text/javascript">!window.jQuery && document.write(unescape(\'%3Cscript src="http://code.jquery.com/jquery-1.9.1.min.js"%3E%3C/script%3E\'))</script>
			<link href="'. $settings['default_theme_url'] .'/css/breeze.css" rel="stylesheet" type="text/css" />';

			// DUH! winning!
			if ($breezeGlobals->getValue('action') == 'profile' && $breezeSettings->enable('admin_settings_enable'))
				$context['insert_after_template'] .= Breeze::who(true);

			$header_done = true;
		}

		// Define some variables for the ajax stuff
		if ($type == 'profile')
		{
			$context['html_headers'] .= '
			<script type="text/javascript"><!-- // --><![CDATA[
				var breeze_error_message = '. JavaScriptEscape($text->getText('error_message')) .';
				var breeze_success_message = '. JavaScriptEscape($text->getText('success_message')) .';
				var breeze_empty_message = '. JavaScriptEscape($text->getText('empty_message')) .';
				var breeze_error_delete = '. JavaScriptEscape($text->getText('error_message')) .';
				var breeze_success_delete = '. JavaScriptEscape($text->getText('success_delete')) .';
				var breeze_confirm_delete = '.JavaScriptEscape( $text->getText('confirm_delete')) .';
				var breeze_confirm_yes = '. JavaScriptEscape($text->getText('confirm_yes')) .';
				var breeze_confirm_cancel = '. JavaScriptEscape($text->getText('confirm_cancel')) .';
				var breeze_already_deleted = '. JavaScriptEscape($text->getText('already_deleted')) .';
				var breeze_cannot_postStatus = '. JavaScriptEscape($text->getText('cannot_postStatus')) .';
				var breeze_cannot_postComments = '. JavaScriptEscape($text->getText('cannot_postComments')) .';
				var breeze_page_loading = '. JavaScriptEscape($text->getText('page_loading')) .';
				var breeze_page_loading_end = '. JavaScriptEscape($text->getText('page_loading_end')) .';
				var breeze_current_user = '. JavaScriptEscape($user_info['id']) .';
				var breeze_infinite_scroll = '. (JavaScriptEscape(!empty($context['member']['options']['Breeze_infinite_scroll']) ? 'string' : '0' )).';
				var breeze_how_many_mentions_options = '. (JavaScriptEscape(!empty($context['member']['options']['Breeze_how_many_mentions_options']) ? $context['member']['options']['Breeze_how_many_mentions_options'] : 5)) .';
		// ]]></script>';

			// Let's load jquery from CDN only if it hasn't been loaded yet
			$context['html_headers'] .= '
			<link href="'. $settings['default_theme_url'] .'/css/facebox.css" rel="stylesheet" type="text/css" />
			<link rel="stylesheet" type="text/css" href="'. $settings['default_theme_url'] .'/css/jquery.atwho.css"/>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/facebox.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/livequery.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/jquery.noty.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/top.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/center.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/topCenter.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.caret.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.atwho.js"></script>';

			// Any tabs?
			if (!empty($context['member']['options']['Breeze_enable_visits_tab']) || !empty($context['member']['options']['Breeze_enable_buddies_tab']))
				$context['html_headers'] .= '<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.idTabs.min.js"></script>';

			// Does the user wants to use infinite scroll?
			if (!empty($context['member']['options']['Breeze_infinite_scroll']))
				$context['html_headers'] .= '
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.infinitescroll.min.js" type="text/javascript"></script>';

			// Load breeze.js untill everyone else is loaded
			$context['html_headers'] .= '
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breeze.js"></script>';
		}

		// Does the admin wants to add more actions?
		if ($breezeSettings->enable('allowedActions'))
			Breeze::$_allowedActions = array_merge(Breeze::$_allowedActions, explode(',', $breezeSettings->getSetting('allowedActions')));

		// Stuff for the notifications, don't show this if we aren't on a specified action
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
				var breeze_error_message = '. JavaScriptEscape($text->getText('error_message')) .';
				var breeze_noti_markasread = '. JavaScriptEscape($text->getText('noti_markasread')) .';
				var breeze_noti_markasread_after = '. JavaScriptEscape($text->getText('noti_markasread_after')) .';
				var breeze_noti_delete = '. JavaScriptEscape($text->getText('general_delete')) .';
				var breeze_noti_delete_after = '. JavaScriptEscape($text->getText('noti_delete_after')) .';
				var breeze_noti_close = '. JavaScriptEscape($text->getText('noti_close')) .';
				var breeze_noti_cancel = '. JavaScriptEscape($text->getText('confirm_cancel')) .';
			// ]]></script>';

			$context['insert_after_template'] .= $notifications->doStream($user_info['id']);
		}

		// Admin bits
		if($type == 'admin')
			$context['html_headers'] .= '
			<script src="'. $settings['default_theme_url'] .'/js/jquery.zrssfeed.js" type="text/javascript"></script>
			<script type="text/javascript">
var breeze_feed_error_message = '. JavaScriptEscape($text->getText('feed_error_message')) .';

$(document).ready(function (){
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
	public static function permissions($permissionGroups, $permissionList)
	{
		$permissionGroups['membergroup']['simple'] = array('breeze_per_simple');
		$permissionGroups['membergroup']['classic'] = array('breeze_per_classic');
		$permissionList['membergroup']['breeze_deleteStatus'] = array(
			false,
			'breeze_per_classic',
			'breeze_per_simple');
		$permissionList['membergroup']['breeze_deleteComments'] = array(
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
	public static function profile($profile_areas)
	{
		global $user_info, $context, $breezeController;

		if (empty($breezeController))
			$breezeController = new BreezeController();

		// Settings are required here
		$gSettings = $breezeController->get('settings');
		$text = $breezeController->get('text');

		// Replace the summary page only if the mod is enable
		if ($gSettings->enable('admin_settings_enable'))
		{
			$profile_areas['info']['areas']['summary'] = array(
				'label' => $text->getText('general_wall'),
				'file' => Breeze::$folder . 'BreezeUser.php',
				'function' => 'breezeWall',
				'permission' => array(
					'own' => 'profile_view_own',
					'any' => 'profile_view_any',
					),
				);

			// If the mod is enable, then create another page for the default profile page
			$profile_areas['info']['areas']['static'] = array(
				'label' => $text->getText('general_summary'),
				'file' => 'Profile-View.php',
				'function' => 'summary',
				'permission' => array(
					'own' => 'profile_view_own',
					'any' => 'profile_view_any',
					),
				);

			// Create the area
			$profile_areas['breeze_profile'] = array(
				'title' => $text->getText('general_my_wall_settings'),
				'areas' => array(),
				);

			// Single Status
			$profile_areas['breeze_profile']['areas']['wallstatus'] = array(
				'label' => $text->getText('user_single_status'),
				'file' => Breeze::$folder .'BreezeUser.php',
				'function' => 'breezeSingle',
				'hidden' => true,
				'permission' => array(
					'own' => 'profile_view_own',
					'any' => 'profile_view_any',
					),
				);

			// User individual settings, show the button if the mod is enable and the user is the profile owner or the user has the permissions to edit other walls
			$profile_areas['breeze_profile']['areas']['breezesettings'] = array(
				'label' => $text->getText('user_settings_name'),
				'file' => Breeze::$folder . 'BreezeUser.php',
				'function' => 'breezeSettings',
				'sc' => 'post',
				'permission' => array(
					'own' => array(
						'profile_view_own',
						),
					),
				);

			// Buddies page
			$profile_areas['breeze_profile']['areas']['breezebuddies'] = array(
				'label' => $text->getText('user_buddysettings_name'),
				'file' => Breeze::$folder . 'BreezeUser.php',
				'function' => 'breezeBuddyRequest',
				'permission' => array('own' => 'profile_view_own', ),
				);

			// Notifications admin page
			$profile_areas['breeze_profile']['areas']['breezenoti'] = array(
				'label' => $text->getText('user_notisettings_name'),
				'file' => Breeze::$folder . 'BreezeUser.php',
				'function' => 'breezeNotifications',
				'permission' => array('own' => 'profile_view_own', ),
				);
		}
		// Done with the hacking...
	}

	/**
	 * Breeze::menu()
	 *
	 * Insert a Wall button on the menu buttons array
	 * @param array $menu_buttons An array containing all possible tabs for the main menu.
	 * @link http://mattzuba.com
	 * @return void
	 */
	public static function menu($menu_buttons)
	{
		global $context;

		// Shh!
		Breeze::who(false);
	}

	/**
	 * Breeze::actions()
	 *
	 * Insert the actions needed by this mod
	 * @param array $actions An array containing all possible SMF actions.
	 * @return void
	 */
	public static function actions($actions)
	{
		// A whole new action just for some ajax calls...
		$actions['breezeajax'] = array(Breeze::$folder . 'BreezeDispatcher.php', 'BreezeDispatcher::dispatch');

		// The general wall
		$actions['wall'] = array(Breeze::$folder . 'BreezeDispatcher.php', 'BreezeDispatcher::dispatch');

		// Replace the buddy action
		$actions['buddy'] = array(Breeze::$folder . 'BreezeDispatcher.php', 'BreezeDispatcher::dispatch');

		// A special action for the buddy request message
		$actions['breezebuddyrequest'] = array(Breeze::$folder . 'BreezeUser.php', 'BreezeUser::buddyMessageSend');
	}

	/**
	 * Breeze::who()
	 *
	 * Used in the credits action
	 * @return string a link for copyright notice
	 */
	public static function who($return = false)
	{
		global $context;

		$actions = Breeze::sGlobals('get');

		// Show this only in pages generated by Breeze, people are already mad because I dare to put a link back to my site .__.
		if ($return == true && ($actions->getValue('action') == 'profile' && $actions->getValue('area') == 'breezebuddies' || $actions->getValue('area') == 'breezenoti' || $actions->getValue('area') == 'breeze') || ($actions->getValue('action') == 'profile' && !$actions->getValue('area')))
			return '<div style="margin:auto; text-align:center"><a href="http://missallsunday.com" title="Free SMF Mods">Breeze mod &copy Suki</a></div>';

		elseif ($return == false && isset($context['current_action']) && $context['current_action'] === 'credits')
			$context['copyrights']['mods'][] = '<a href="http://missallsunday.com" title="Free SMF Mods">Breeze mod &copy Suki</a>';
	}

	// It's all about Admin settings from now on

	/**
	 * Breeze::admin()
	 *
	 * @param array $admin_menu An array with all the admin settings buttons
	 * @return
	 */
	public static function admin($admin_menu)
	{
		global $breezeController;

		$text = $breezeController->get('text');

		$admin_menu['config']['areas']['breezeadmin'] = array(
			'label' => $text->getText('admin_settings_main'),
			'file' => 'Breeze/BreezeAdmin.php',
			'function' => 'Breeze_Admin_Index',
			'icon' => 'administration.gif',
			'subsections' => array(
				'general' => array($text->getText('admin_settings_main')),
				'settings' => array($text->getText('admin_settings_settings')),
				'permissions' => array($text->getText('admin_settings_sub_permissions')),
				'style' => array($text->getText('admin_settings_sub_style')),
				'donate' => array($text->getText('admin_settings_donate')),
			),
		);
	}

	public static function credits()
	{
		// Dear contributor, please feel free to add yourself here
		$credits = array(
			'dev' => array(
				'name' => 'Developer(s)',
				'users' => array(
					'suki' => array(
						'name' => 'Jessica "Suki" Gonz&aacute;lez',
						'site' => 'http://missallsunday.com',
					),
				),
			),
			'scripts' => array(
				'name' => 'Third Party Scripts',
				'users' => array(
					'facebox' => array(
						'name' => 'Facebox',
						'site' => 'https://github.com/defunkt/facebox',
					),
					'feed' => array(
						'name' => 'zRSSFeeds',
						'site' => 'http://www.zazar.net/developers/jquery/zrssfeed',
					),
					'live_query' => array(
						'name' => 'Live query plugin',
						'site' => 'http://brandonaaron.net/code/livequery/docs',
					),
					'noty' => array(
						'name' => 'noty jquery plugin',
						'site' => 'http://needim.github.com/noty/',
					),
					'scroll' => array(
						'name' => 'infinite-scroll',
						'site' => 'https://github.com/paulirish/infinite-scroll',
					),
					'mentions' => array(
						'name' => 'Mentions autocomplete',
						'site' => 'http://ichord.github.com/At.js',
					),
				),
			),
		);

		return $credits;
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
