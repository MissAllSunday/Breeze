<?php

/**
 * Breeze
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
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
	public static $version = '1.0';
	public static $folder = '/Breeze/';
	public static $txtpattern = 'Breeze_';
	public static $permissions = array('deleteComments', 'deleteOwnComments', 'deleteProfileComments', 'deleteStatus', 'deleteOwnStatus', 'deleteProfileStatus', 'postStatus', 'postComments', 'canMention', 'beMentioned');
	public static $allSettings = array('wall', 'general_wall', 'pagination_number', 'load_more', 'how_many_mentions', 'kick_ignored', 'activityLog', 'buddies', 'visitors', 'visitors_timeframe', 'clear_noti', 'noti_on_comment', 'noti_on_mention', 'gender', 'buddiesList', 'ignoredList', 'profileViews',);

	// Support site feed
	public static $supportSite = 'http://missallsunday.com/index.php?action=.xml;sa=news;board=11;limit=10;type=rss2';

	// Its easier to list the allowed actions
	public static $_allowedActions = array('wall', 'display', 'unread', 'unreadreplies', 'viewprofile', 'profile', 'who', 'credits',);

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
	 * Breeze::data()
	 *
	 * A new instance of BreezeGlobals.
	 * @param string $var Either post, request or get
	 * @return object Access to BreezeGlobals
	 */
	public static function data($var)
	{
		return new BreezeData($var);
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
		$permissionGroups['membergroup']['simple'] = array('breeze_per_simple');
		$permissionGroups['membergroup']['classic'] = array('breeze_per_classic');

		foreach (Breeze::$permissions as $p)
			$permissionList['membergroup']['breeze_'. $p] = array(
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
		global $user_info, $context, $breezeController, $memID;

		// Safety
		if (empty($breezeController))
			$breezeController = new BreezeController();

		// General settings are required here
		$tools = $breezeController->get('tools');

		// Replace the summary page only if the mod is enable
		if ($tools->enable('master'))
		{
			// We need your settings...
			$userSettings = $breezeController->get('query')->getUserSettings($user_info['id']);

			$profile_areas['info']['areas']['summary'] = array(
				'label' => $tools->text('general_wall'),
				'file' => Breeze::$folder . 'BreezeUser.php',
				'function' => 'breezeWall',
				'permission' => array(
					'own' => 'profile_view_own',
					'any' => 'profile_view_any',
					),
				);

			// If the mod is enable, then create another page for the default profile page
			$profile_areas['info']['areas']['static'] = array(
				'label' => $tools->text('general_summary'),
				'file' => 'Profile-View.php',
				'function' => 'summary',
				'permission' => array(
					'own' => 'profile_view_own',
					'any' => 'profile_view_any',
					),
				);

			// Create the area
			$profile_areas['breeze_profile'] = array(
				'title' => $tools->text('general_my_wall_settings'),
				'areas' => array(),
			);

			// User individual settings, show the button if the mod is enable and the user is the profile owner or the user has the permissions to edit other walls
			$profile_areas['breeze_profile']['areas']['breezesettings'] = array(
				'label' => $tools->text('user_settings_name'),
				'file' => Breeze::$folder . 'BreezeUser.php',
				'function' => 'breezeSettings',
				'permission' => array(
					'own' => array(
						'profile_view_own',
						),
					),
				);

			// Notification's settings.
			if ($tools->enable('notifications'))
			{
				$profile_areas['breeze_profile']['areas']['breezenotisettings'] = array(
					'label' => $tools->text('user_settings_name_settings'),
					'file' => Breeze::$folder . 'BreezeUser.php',
					'function' => 'breezenotisettings',
					'permission' => array(
						'own' => array(
							'profile_view_own',
							),
						),
					);

				// Notifications admin page
				$profile_areas['breeze_profile']['areas']['breezenoti'] = array(
					'label' => $tools->text('user_notisettings_name'),
					'file' => Breeze::$folder . 'BreezeUser.php',
					'function' => 'breezeNotifications',
					'subsections' => array(),
					'permission' => array('own' => 'profile_view_own', ),
					);
			}

			// Logs anyone?
			if ($userSettings['activityLog'])
				$profile_areas['breeze_profile']['areas']['breezelogs'] = array(
					'label' => $tools->text('user_notilogs_name'),
					'file' => Breeze::$folder . 'BreezeUser.php',
					'function' => 'breezeNotiLogs',
					'subsections' => array(),
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
	public static function menu(&$menu_buttons)
	{
		global $context, $breezeController, $txt, $scripturl, $user_info;

		if (empty($breezeController))
			$breezeController = new BreezeController();

		$tools = $breezeController->get('tools');
		$userSettings = $breezeController->get('query')->getUserSettings($user_info['id']);

		// Display common css and js files.
		Breeze::notiHeaders();

		// Replace the duplicate profile button
		if ($tools->enable('master') && !empty($menu_buttons['profile']['sub_buttons']['summary']))
			$menu_buttons['profile']['sub_buttons']['summary'] = array(
				'title' => $txt['summary'],
				'href' => $scripturl . '?action=profile;area=static',
				'show' => true,
			);

		// The Wall link
		$insert = 'home'; // for now lets use the home button as reference...
		$counter = 0;

		foreach ($menu_buttons as $area => $dummy)
			if (++$counter && $area == $insert )
				break;

		$menu_buttons = array_merge(
			array_slice($menu_buttons, 0, $counter),
			array('wall' => array(
				'title' => $tools->text('general_wall'),
				'href' => $scripturl . '?action=wall',
				'show' => ($tools->enable('master') && !$user_info['is_guest'] && !empty($userSettings['general_wall'])),
				'sub_buttons' => array(
					'noti' => array(
						'title' => $tools->text('user_notisettings_name'),
						'href' => $scripturl . '?action=profile;area=breezenoti',
						'show' => ($tools->enable('master') && !$user_info['is_guest']),
						'sub_buttons' => array(),
						),
					'admin' => array(
						'title' => $tools->text('admin'),
						'href' => $scripturl . '?action=admin;area=breezeadmin',
						'show' => ($tools->enable('master') && $user_info['is_admin']),
						'sub_buttons' => array(),
						),
					),
				),
			),
			array_slice($menu_buttons, $counter)
		);

		// DUH! winning!
		Breeze::who();
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
		// A whole new action just for some ajax calls. Actually, a pretty good chunk of Breeze transactions come through here so...
		$actions['breezeajax'] = array(Breeze::$folder . 'BreezeDispatcher.php', 'BreezeDispatcher::dispatch');

		// The general wall
		$actions['wall'] = array(Breeze::$folder . 'BreezeDispatcher.php', 'BreezeDispatcher::dispatch');

		// Replace the buddy action @todo for next version
		// $actions['buddy'] = array(Breeze::$folder . 'BreezeDispatcher.php', 'BreezeDispatcher::dispatch');

		// A special action for the buddy request message
		$actions['breezebuddyrequest'] = array(Breeze::$folder . 'BreezeUser.php', 'breezeBuddyMessage');
	}

	/**
	 * Breeze::topic()
	 *
	 * Creates a new notification when someone opens a new topic.
	 * @param array $msgOptions message info.
	 * @param array $topicOptions topic info.
	 * @param array $posterInfo poster info ...DUH!
	 * @return
	 */
	public static function newTopic($msgOptions, $topicOptions, $posterOptions)
	{
		global $context, $breezeController, $txt, $scripturl, $user_info;

		if (empty($breezeController))
			$breezeController = new BreezeController();

		// We need the almighty power of breezeController!
		$noti = $breezeController->get('notifications');
		$userSettings = $breezeController->get('query')->getUserSettings($user_info['id']);

		// Cheating, lets insert the notification directly, do it only if the topic was approved
		if ($topicOptions['is_approved'] && !empty($userSettings['activityLog']))
			$noti->create(array(
				'sender' => $posterOptions['id'],
				'receiver' => $posterOptions['id'],
				'type' => 'logTopic',
				'time' => time(),
				'viewed' => 3, // 3 is a special case to indicate that this is a log entry, cannot be seen or unseen
				'content' => array(
					'posterName' => $posterOptions['name'],
					'topicId' => $topicOptions['id'],
					'subject' => $msgOptions['subject'],
				),
				'type_id' => $topicOptions['id'],
				'second_type' => 'topics',
			));
	}

	/**
	 * Breeze::newRegister()
	 *
	 * Creates a new notification for new registrations
	 * @param array $regOptions An array containing info about the new member.
	 * @param int $user_id the newly created user ID.
	 * @return
	 */
	public static function newRegister($regOptions, $user_id)
	{
		global $context, $breezeController, $txt, $scripturl, $user_info;

		if (empty($breezeController))
			$breezeController = new BreezeController();

		// We need the almighty power of breezeController!
		$noti = $breezeController->get('notifications');

		// Cheating, lets insert the notification directly, do it only if the topic was approved
		if ($topicOptions['is_approved'])
			$noti->create(array(
				'sender' => $user_id,
				'receiver' => $user_id,
				'type' => 'register',
				'time' => time(),
				'viewed' => 3, // 3 is a special case to indicate that this is a log entry, cannot be seen or unseen
				'content' => function() use ($regOptions, $scripturl, $text, $scripturl, $user_id)
					{
						return '<a href="'. $scripturl .'?action=profile;u='. $user_id . '">'. $regOptions['username'] .'</a> '. $tools->text('logRegister');
					},
				'type_id' => 0,
				'second_type' => 'register',
			));
	}

	/**
	 * Breeze::notiHeaders()
	 *
	 * Static method used to embed the JavaScript and other bits of code on every page inside SMF.
	 * @return void
	 */
	public static function notiHeaders()
	{
		global $context, $user_info, $breezeController, $settings;
		static $header_done = false;

		// Don't do anything if we are in SSI world
		if (SMF == 'SSI')
			return false;

		if (empty($breezeController))
			$breezeController = new BreezeController();

		// Prevent this from showing twice
		if (!$header_done)
		{
			$tools = $breezeController->get('tools');
			$breezeGlobals = Breeze::data('get');
			$userSettings = $breezeController->get('query')->getUserSettings($user_info['id']);

			// Don't pass the "about me" stuff...
			if (!empty($userSettings['aboutMe']))
				unset($userSettings['aboutMe']);

			// Define some variables for the ajax stuff
			if (!$user_info['is_guest'])
			{
				$jsVars = array('feed_error_message', 'error_server', 'error_wrong_values', 'success_published', 'success_published_comment', 'error_empty', 'success_delete_status', 'success_delete_comment', 'confirm_delete', 'confirm_yes', 'confirm_cancel', 'error_already_deleted_status', 'error_already_deleted_comment', 'error_already_deleted_noti', 'error_already_marked_noti', 'cannot_postStatus', 'cannot_postComments', 'error_no_valid_action', 'error_no_access', 'success_noti_unmarkasread_after', 'success_noti_markasread_after', 'error_noti_markasreaddeleted_after', 'error_noti_markasreaddeleted', 'success_noti_delete_after', 'success_noti_visitors_clean',  'success_notiMulti_delete_after', 'success_notiMulti_markasread_after', 'success_notiMulti_unmarkasread_after', 'noti_markasread', 'noti_delete', 'noti_cancel', 'noti_closeAll', 'load_more', 'page_loading_end', 'page_loading');

				$context['html_headers'] .= '
	<script type="text/javascript"><!-- // --><![CDATA[

		// The main breeze JS object.
		var breeze = {
			text : {},
			settings : {},
			ownerSettings : {},
			currentSettings : {},
			tools : {},
			pagination : {},
			currentUser : '. $user_info['id'] .',
			session : {
				id : ' . JavaScriptEscape($context['session_id']) . ',
				v : ' . JavaScriptEscape($context['session_var']) . ',
			},
		};';

				// Populate the text object with all possible text vars this mod uses and there are a lot!
				foreach ($jsVars as $var)
				$context['html_headers'] .= '
		breeze.text.'. $var .' = '. JavaScriptEscape($tools->text($var));

				// Since where here already, load the current User (currentSettings) object
				foreach (Breeze::$allSettings as $k)
					$context['html_headers'] .= '
		breeze.currentSettings.'. $k .' = '. (isset($userSettings[$k]) ? (is_array($userSettings[$k]) ? json_encode($userSettings[$k]) : JavaScriptEscape($userSettings[$k])) : 'false') .';';

				$context['html_headers'] .= '
	// ]]></script>';
			}

			// Common css and js files.
			$context['html_headers'] .= '
	<script type="text/javascript">!window.jQuery && document.write(unescape(\'%3Cscript src="http://code.jquery.com/jquery-1.9.1.min.js"%3E%3C/script%3E\'))</script>
	<link href="'. $settings['default_theme_url'] .'/css/facebox.css" rel="stylesheet" type="text/css" />
	<link href="'. $settings['default_theme_url'] .'/css/breeze.css" rel="stylesheet" type="text/css" />
	<link href="'. $settings['default_theme_url'] .'/css/jquery.atwho.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="'. $settings['default_theme_url'] .'/css/jquery.atwho.css"/>';

			// Load the notification JS files.
			if (!$user_info['is_guest'])
			{
				$context['insert_after_template'] .= '
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/facebox.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.hashchange.min.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/jquery.noty.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/top.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/topLeft.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/topRight.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/themes/default.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breezeNoti.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breeze.js"></script>';

				// Does the admin wants to add more actions?
				if ($tools->enable('allowed_actions'))
					Breeze::$_allowedActions = array_merge(Breeze::$_allowedActions, explode(',', $tools->setting('allowed_actions')));

				// Stuff for the notifications, don't show this if we aren't on a specified action
				if ($tools->enable('notifications') && empty($user_info['is_guest']) && (in_array($breezeGlobals->get('action'), Breeze::$_allowedActions) || $breezeGlobals->get('action') == false))
				{
					$notifications = $breezeController->get('notifications');
					$context['insert_after_template'] .= '
	<script type="text/javascript"><!-- // --><![CDATA[
		breeze.tools.stream('. $user_info['id'] .');
	// ]]></script>';

					// If someone wants to do something with all this info, let them...
					$context['Breeze']['notifications'] = $notifications->getMessages();
				}
			}

			$header_done = true;
		}
	}

	/**
	 * Breeze::who()
	 *
	 * Used in the credits action.
	 * @param boolean $return decide between returning a string or append it to a known context var.
	 * @return string a link for copyright notice
	 */
	public static function who($return = false)
	{
		global $context;

		// Show this only in pages generated by Breeze.
		if ($return)
			return '<div style="margin:auto; text-align:center" class="clear"><a href="http://missallsunday.com" title="Free SMF Mods">Breeze mod &copy Suki</a></div>';

		elseif (!$return && isset($context['current_action']) && $context['current_action'] === 'credits')
			$context['copyrights']['mods'][] = '<a href="http://missallsunday.com" title="Free SMF Mods">Breeze mod &copy Suki</a>';
	}

	// It's all about Admin settings from now on

	/**
	 * Breeze::admin()
	 *
	 * Creates a new notification everytime an user creates a new topic
	 * @param array $admin_menu An array with all the admin settings buttons
	 * @return
	 */
	public static function admin(&$admin_menu)
	{
		global $breezeController;

		// Time to overheat the server, yay!
		if (empty($breezeController))
			$breezeController = new BreezeController();

		$tools = $breezeController->get('tools');

		$admin_menu['config']['areas']['breezeadmin'] = array(
			'label' => $tools->adminText('page_main'),
			'file' => 'Breeze/BreezeAdmin.php',
			'function' => 'Breeze_Admin_Index',
			'icon' => 'administration.gif',
			'subsections' => array(
				'general' => array($tools->adminText('page_main')),
				'settings' => array($tools->adminText('page_settings')),
				'permissions' => array($tools->adminText('page_permissions')),
				'style' => array($tools->adminText('page_style')),
				'donate' => array($tools->adminText('page_donate')),
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
					'jQuery' => array(
						'name' => 'jQuery',
						'site' => 'http://jquery.com/',
					),
					'facebox' => array(
						'name' => 'Facebox',
						'site' => 'https://github.com/defunkt/facebox',
					),
					'feed' => array(
						'name' => 'zRSSFeeds',
						'site' => 'http://www.zazar.net/developers/jquery/zrssfeed',
					),
					'noty' => array(
						'name' => 'noty jquery plugin',
						'site' => 'http://needim.github.com/noty/',
					),
					'mentions' => array(
						'name' => 'Mentions autocomplete',
						'site' => 'http://ichord.github.com/At.js',
					),
				),
			),
			'images' => array(
				'name' => 'Icons',
				'users' => array(
					'ikons' => array(
						'name' => 'ikons from Piotr Kwiatkowski',
						'site' => 'http://ikons.piotrkwiatkowski.co.uk/',
					),
				),
			),
		);

		// Oh well, one can dream...
		call_integration_hook('integrate_breeze_credits', array(&$credits));

		return $credits;
	}
}

/*
* And so it is
* Just like you said it would be
* We'll both forget the breeze
* Most of the time
* And so it is
* The shorter story
* No love, no glory
* No hero in her skies
*/
