<?php

/**
 * Breeze
 *
 * @package Breeze mod
 * @version 1.1
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
 *
 * @return bool
 */
function breeze_autoloader($class_name)
{
	global $sourcedir;

	$file_path = $sourcedir . '/Breeze/' . $class_name . '.php';

	if (file_exists($file_path))
		require_once ($file_path);

	else
		return false;
}

spl_autoload_register('breeze_autoloader');


class Breeze extends Pimple
{
	protected $_services = array('admin', 'ajax', 'display', 'form', 'log', 'mention', 'notifications', 'parser', 'query', 'tools', 'user', 'userInfo', 'wall',);
	public static $name = 'Breeze';
	public static $version = '1.1';
	public static $folder = '/Breeze/';
	public static $coversFolder = '/breezeFiles/';
	public static $txtpattern = 'Breeze_';
	public static $permissions = array('deleteComments', 'deleteOwnComments', 'deleteProfileComments', 'deleteStatus', 'deleteOwnStatus', 'deleteProfileStatus', 'postStatus', 'postComments', 'canMention', 'beMentioned', 'canCover');
	public static $allSettings = array('wall', 'general_wall', 'pagination_number', 'load_more', 'how_many_mentions', 'kick_ignored', 'activityLog', 'buddies', 'visitors', 'visitors_timeframe', 'clear_noti', 'noti_on_comment', 'noti_on_mention', 'gender', 'buddiesList', 'ignoredList', 'profileViews',);

	// Support site feed
	public static $supportSite = 'http://missallsunday.com/index.php?action=.xml;sa=news;board=11;limit=10;type=rss2';

	// Its easier to list the allowed actions
	public static $_allowedActions = array('wall', 'display', 'unread', 'unreadreplies', 'viewprofile', 'profile', 'who', 'credits',);

	/**
	 * Breeze::__construct()
	 *
	 * @return \Breeze
	 */
	public function __construct()
	{
		parent::__construct();
		$this->set();
	}

	/**
	 * Breeze::set()
	 *
	 * @return void
	 */
	protected function set()
	{
		foreach($this->_services as $s)
		{
			$this[$s] = function ($c) use ($s)
			{
				$call = Breeze::$name . ucfirst($s);
				return new $call($c);
			};
		}
	}

	/**
	 * Breeze::get()
	 *
	 * A short-cut method to get access to services
	 * @param string $id the name of the service to retrieve.
	 * @return object an instance of the service.
	 */
	public function get($id)
	{
		if (!isset($this[$id]))
			fatal_lang_error('Breeze_error_no_property', false, array($id));

		if (is_callable($this[$id]))
			return $this[$id]($this);

		else
			return $this[$id];
	}

	/**
	 * Breeze::load()
	 *
	 * @param string $file a comma separated list of all the file names to be loaded.
	 *
	 * @return void
	 */
	public function load($file)
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
	public function permissions(&$permissionGroups, &$permissionList)
	{
		// We gotta load our language file.
		loadLanguage(Breeze::$name);

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
	public function profile(&$profile_areas)
	{
		global $user_info, $context;

		// General settings are required here
		$tools = $this['tools'];

		// Replace the summary page only if the mod is enable
		if ($tools->enable('master'))
		{
			// We need your settings...
			$userSettings = $this['query']->getUserSettings($context['member']['id']);

			if ($tools->enable('force_enable') || !empty($userSettings['wall']))
			{
				$profile_areas['info']['areas']['summary'] = array(
					'label' => $tools->text('general_wall'),
					'file' => Breeze::$folder . 'BreezeUser.php',
					'function' => 'wall',
					'class' => 'BreezeUser',
					'permission' => array(
						'own' => 'is_not_guest',
						'any' => 'profile_view',
					),
				);

				// If the mod is enable, then create another page for the default profile page
				$profile_areas['info']['areas']['static'] = array(
					'label' => $tools->text('general_summary'),
					'file' => 'Profile-View.php',
					'function' => 'summary',
					'permission' => array(
						'own' => 'is_not_guest',
						'any' => 'profile_view',
					),
				);
			}

			// Create the area
			$profile_areas['breeze_profile'] = array(
				'title' => $tools->text('general_my_wall_settings'),
				'areas' => array(),
			);

			// User individual settings, show the button if the mod is enable and the user is the profile owner.
			$profile_areas['breeze_profile']['areas']['breezesettings'] = array(
				'label' => $tools->text('user_settings_name'),
				'file' => Breeze::$folder . 'BreezeUser.php',
				'function' => 'settings',
				'class' => 'BreezeUser',
				'permission' => array(
					'own' => 'profile_identity_own',
					'any' => false,
				),
			);

			// Notification's settings.
			if ($tools->enable('notifications'))
			{
				$profile_areas['breeze_profile']['areas']['breezenotisettings'] = array(
					'label' => $tools->text('user_settings_name_settings'),
					'file' => Breeze::$folder . 'BreezeUser.php',
					'function' => 'notiSettings',
					'class' => 'BreezeUser',
					'permission' => array(
						'own' => 'profile_identity_own',
						'any' => false,
					),
				);

				// Notifications admin page
				$profile_areas['breeze_profile']['areas']['breezenoti'] = array(
					'label' => $tools->text('user_notisettings_name'),
					'file' => Breeze::$folder . 'BreezeUser.php',
					'function' => 'notifications',
					'class' => 'BreezeUser',
					'subsections' => array(),
					'permission' => array(
						'own' => 'profile_identity_own',
						'any' => false,
					),
				);
			}

			// Logs anyone?
			if (!empty($userSettings['activityLog']))
				$profile_areas['breeze_profile']['areas']['breezelogs'] = array(
					'label' => $tools->text('user_notilogs_name'),
					'file' => Breeze::$folder . 'BreezeUser.php',
					'function' => 'notiLogs',
					'class' => 'BreezeUser',
					'subsections' => array(),
					'permission' => array(
						'own' => 'profile_identity_own',
						'any' => false,
					),
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
	public function menu(&$menu_buttons)
	{
		global $context, $txt, $scripturl, $user_info;

		$tools = $this['tools'];
		$userSettings = $this['query']->getUserSettings($user_info['id']);

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
	public function actions(&$actions)
	{
		// Fool the system and directly inject the main object to breezeAjax and breezeWall, Breeze's final classes

		// A whole new action just for some ajax calls. Actually, a pretty good chunk of Breeze transactions come through here so...
		$actions['breezeajax'] = array(Breeze::$folder . 'Breeze.php', 'Breeze::call#');

		// The general wall
		$actions['wall'] = array(Breeze::$folder . 'Breeze.php', 'Breeze::call#');

		// Replace the buddy action @todo for next version
		// $actions['buddy'] = array(Breeze::$folder . 'BreezeDispatcher.php', 'BreezeDispatcher::dispatch');

		// A special action for the buddy request message
		$actions['breezebuddyrequest'] = array(Breeze::$folder . 'BreezeUser.php', 'breezeBuddyMessage');
	}

	public function likes($type, $content, $sa, $js, $extra)
	{
		// Create our returned array
		$data = array();

		// @temp for testing.
		$data['can_see'] = true;
		$data['can_like'] = true;
		$data['type'] = $type;
		$data['flush_cache'] = true;
		$data['callback'] = 'Breeze::likesUpdate#';

		// $extra has my much needed "comming from" json string, without this I cannot redirect users properly.
		// $redirect = json_decode($extra, true);
		$data['redirect'] = 'lol';

		return $data;
	}

	public function likesUpdate($type, $content, $numLikes, $already_liked)
	{
		// The likes system only accepts 6 characters so convert that weird id into a more familiar one...
		$convert = array('breSta' => 'status', 'breCom' => 'comments');

		$this['query']->updateLikes($convert[$type], $content, $numLikes);

		// @todo this is a nice place to fire up some notifications...
	}

	public function handleLikes($type, $content)
	{
		$data = array();
		$convert = array('breSta' => 'status', 'breCom' => 'comments');

		// Don't bother with any other like types...
		if (!in_array($type, array_keys($convert)))
			return false;

		$row = $convert[$type] .'_id';
		$authorColumn = $convert[$type] .'_poster_id';

		// With the given values, try to find who is the owner of the liked content.
		$data = $this['query']->getSingleValue($convert[$type], $row, $content);

		if (!empty($data[$authorColumn]))
			return $data[$authorColumn];

		// Return false if the status/comment is no longer on the DB.
		else
			return false;
	}

	/**
	 * Breeze::call()
	 *
	 * Wrapper method to call Breeze methods while maintaining dependency injection.
	 * @return void
	 */
	public function call()
	{
		// Just some quick code to make sure this works...
		$a = array('wall', 'ajax');
		$action = Breeze::data('get')->get('action');

		// Gotta remove the "breeze" from breezeajax.
		if ($action == 'breezeajax')
			$action = str_replace('breeze', '', $action);

		if (in_array($action, $a))
			$this[$action]->call();
	}

	/**
	 * Breeze::topic()
	 *
	 * Creates a new notification when someone opens a new topic.
	 *
	 * @param array $msgOptions   message info.
	 * @param array $topicOptions topic info.
	 * @param array $posterOptions poster info.
	 *
	 * @return void
	 */
	public function newTopic($msgOptions, $topicOptions, $posterOptions)
	{
		global $context, $txt, $scripturl, $user_info;

		// We don't need the almighty power of breezeController anymore!
		$noti = $this['notifications'];
		$userSettings = $this['query']->getUserSettings($user_info['id']);

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
					'board' => $topicOptions['board'],
				),
				'type_id' => $topicOptions['id'],
				'second_type' => 'topics',
			));
	}

	/**
	 * Breeze::newRegister()
	 *
	 * Creates a new notification for new registrations
	 *
	 * @param array $regOptions An array containing info about the new member.
	 * @param int   $user_id    the newly created user ID.
	 *
	 * @return void
	 */
	public static function newRegister($regOptions, $user_id)
	{
		global $context, $txt, $scripturl, $user_info;

		// We need the almighty power of breezeController!
		$noti = $this['notifications'];

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
	 * Used to embed the JavaScript and other bits of code on every page inside SMF.
	 * @return void
	 */
	public function notiHeaders()
	{
		global $context, $user_info, $settings;
		static $header_done = false;

		// Don't do anything if we are in SSI world
		if (SMF == 'SSI')
			return false;

		// Prevent this from showing twice
		if (!$header_done)
		{
			$tools = $this['tools'];
			$breezeGlobals = Breeze::data('get');
			$userSettings = $this['query']->getUserSettings($user_info['id']);

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

				// Since we're here already, load the current User (currentSettings) object
				foreach (Breeze::$allSettings as $k)
					$context['html_headers'] .= '
		breeze.currentSettings.'. $k .' = '. (isset($userSettings[$k]) ? (is_array($userSettings[$k]) ? json_encode($userSettings[$k]) : JavaScriptEscape($userSettings[$k])) : 'false') .';';

				$context['html_headers'] .= '
	// ]]></script>';
			}

			// Common css and js files.
			$context['html_headers'] .= '
	<script type="text/javascript">!window.jQuery && document.write(unescape(\'%3Cscript src="http://code.jquery.com/jquery-1.9.1.min.js"%3E%3C/script%3E\'))</script>
	<link href="'. $settings['default_theme_url'] .'/css/breeze.min.css" rel="stylesheet" type="text/css" />';

			// Load the notification JS files.
			if (!$user_info['is_guest'])
			{
				$context['insert_after_template'] .= '
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/facebox.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/moment.min.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/livestamp.min.js"></script>
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
					$notifications = $this['notifications'];
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
	public function who($return = false)
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
	 * Creates a new section in the admin panel.
	 *
	 * @param array $admin_menu An array with all the admin settings buttons
	 *
	 * @return void
	 */
	public function admin(&$admin_menu)
	{
		global $breezeController;

		$tools = $this['tools'];

		$admin_menu['config']['areas']['breezeadmin'] = array(
			'label' => $tools->adminText('page_main'),
			'file' => 'Breeze/BreezeAdmin.php',
			'function' => 'call',
			'class' => 'BreezeAdmin',
			'icon' => 'packages.png',
			'subsections' => array(
				'general' => array($tools->adminText('page_main')),
				'settings' => array($tools->adminText('page_settings')),
				'permissions' => array($tools->adminText('page_permissions')),
				'donate' => array($tools->adminText('page_donate')),
			),
		);
	}

	/**
	 * @return array
	 */
	public function credits()
	{
		// Dear contributor, please feel free to add yourself here.
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
					'moment' => array(
						'name' => 'moment.js',
						'site' => 'http://momentjs.com/',
					),
					'livestamp' => array(
						'name' => 'Livestamp.js',
						'site' => 'http://mattbradley.github.io/livestampjs/',
					),
					'fileUpload' => array(
						'name' => 'jQuery File Upload Plugin',
						'site' => 'https://github.com/blueimp/jQuery-File-Upload',
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
