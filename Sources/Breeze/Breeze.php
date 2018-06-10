<?php

/**
 * Breeze
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011 - 2017, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

namespace Breeze;

if (!defined('SMF'))
	die('No direct access...');

// Still need to manually load Pimple :(
require_once $sourcedir . '/Breeze/Pimple/Container.php';

class Breeze extends \Pimple\Container
{
	protected $_services = [
		'admin',
		'ajax',
		'alerts',
		'buddy',
		'data',
		'display',
		'form',
		'log',
		'noti',
		'query',
		'tools',
		'user',
		'userInfo',
		'wall',
		'mood',
	];
	public static $name = 'Breeze';
	public static $version = '1.1';
	public static $folder = '/Breeze/';
	public static $coversFolder = '/breezeFiles/';
	public $txtpattern = 'Breeze_';
	public static $permissions = [
		'deleteComments',
		'deleteOwnComments',
		'deleteProfileComments',
		'deleteStatus',
		'deleteOwnStatus',
		'deleteProfileStatus',
		'postStatus',
		'postComments',
		'canCover',
		'canMood'
	];
	public static $allSettings = array(
		'wall' => 'CheckBox',
		'general_wall' => 'CheckBox',
		'pagination_number' => 'Int',
		'number_alert' => 'Int',
		'load_more' => 'CheckBox',
		'activityLog' => 'CheckBox',
		'kick_ignored' => 'CheckBox',
		'blockList' => 'Text',
		'buddies' => 'CheckBox',
		'how_many_buddies' => 'Int',
		'visitors' => 'CheckBox',
		'how_many_visitors' => 'Int',
		'clear_noti' => 'HTML',
		'aboutMe' => 'TextArea',
		'cover_height' => 'Int',
	);
	public $likeTypes = ['breSta' => 'status', 'breCom' => 'comments'];
	public $trackHooks = array(
		'integrate_create_topic' => 'createTopic',
		'integrate_profile_save' => 'editProfile',
	);
	public $wrapperActions = ['wall', 'ajax', 'admin', 'mood', 'buddy'];

	// Support site feed
	public static $supportSite = 'https://github.com/MissAllSunday/Breeze/releases.atom';

	/**
	 * \Breeze\Breeze::__construct()
	 *
	 * @return \Breeze
	 */
	public function __construct()
	{
		parent::__construct();
		$this->set();
	}

	public function autoLoad(&$classMap)
	{
		$classMap['Breeze\\'] = 'Breeze/';
	}

	/**
	 * \Breeze\Breeze::set()
	 *
	 * @return void
	 */
	protected function set()
	{
		foreach($this->_services as $s)
		{
			$this[$s] = function ($c) use ($s)
			{
				$call = __NAMESPACE__ . '\\'. \Breeze\Breeze::$name . ucfirst($s);
				return new $call($c);
			};
		}
	}

	/**
	 * \Breeze\Breeze::get()
	 *
	 * A short-cut method to get access to services
	 * @param string $id the name of the service to retrieve.
	 * @return object an instance of the service.
	 */
	public function get($id)
	{
		if (!isset($this[$id]))
			fatal_lang_error('Breeze_error_no_property', false, [$id]);

		if (is_callable($this[$id]))
			return $this[$id]($this);

		else
			return $this[$id];
	}

	/**
	 * \Breeze\Breeze::load()
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
	 * $this->data()
	 *
	 * A new instance of BreezeGlobals.
	 * @param string $var Either post, request or get
	 * @return object Access to BreezeGlobals
	 */
	public static function data($var = false)
	{
		return new BreezeData($var = false);
	}

	/**
	 * \Breeze\Breeze::permissions()
	 *
	 * There is only permissions to post new status and comments on any profile because people needs to be able to post in their own profiles by default the same goes for deleting, people are able to delete their own status/comments on their own profile page.
	 * @param array $permissionGroups An array containing all possible permissions groups.
	 * @param array $permissionList An associative array with all the possible permissions.
	 * @return void
	 */
	public function permissions(&$permissionGroups, &$permissionList)
	{
		// We gotta load our language file.
		loadLanguage(\Breeze\Breeze::$name);

		$permissionGroups['membergroup']['simple'] = ['breeze_per_simple'];
		$permissionGroups['membergroup']['classic'] = ['breeze_per_classic'];

		foreach (\Breeze\Breeze::$permissions as $p)
			$permissionList['membergroup']['breeze_'. $p] = array(
			false,
			'breeze_per_classic',
			'breeze_per_simple');
	}

	/**
	 * \Breeze\Breeze::profile()
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
					'icon' => 'smiley',
					'file' => \Breeze\Breeze::$folder . 'BreezeUser.php',
					'function' => __NAMESPACE__ .'\BreezeUser::userWall#',
					'permission' => array(
						'own' => 'is_not_guest',
						'any' => 'profile_view',
					),
				);

				// If the mod is enable, then create another page for the default profile page
				$profile_areas['info']['areas']['static'] = array(
					'label' => $tools->text('general_summary'),
					'icon' => 'members',
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
				'areas' => [],
			);

			// User individual settings, show the button if the mod is enable and the user is the profile owner.
			$profile_areas['breeze_profile']['areas']['breezesettings'] = array(
				'label' => $tools->text('user_settings_name'),
				'icon' => 'maintain',
				'file' => \Breeze\Breeze::$folder . 'BreezeUser.php',
				'function' => __NAMESPACE__ .'\BreezeUser::userSettings#',
				'enabled' => $context['user']['is_owner'],
				'permission' => array(
					'own' => 'is_not_guest',
					'any' => 'profile_view',
				),
			);

			// Inner alert settings page.
			$profile_areas['breeze_profile']['areas']['alerts'] = array(
				'label' => $tools->text('user_settings_name_alerts'),
				'file' => \Breeze\Breeze::$folder . 'BreezeUser.php',
				'function' => __NAMESPACE__ .'\BreezeUser::userAlerts#',
				'enabled' => $context['user']['is_owner'],
				'icon' => 'maintain',
				'subsections' => array(
					'settings' => array($tools->text('user_settings_name_alerts_settings'), array('is_not_guest', 'profile_view')),
					'edit' => array($tools->text('user_settings_name_alerts_edit'), array('is_not_guest', 'profile_view')),
				),
				'permission' => array(
					'own' => 'is_not_guest',
					'any' => 'profile_view',
				),
			);

			// Cover settings page.
			if ($tools->enable('cover'))
				$profile_areas['breeze_profile']['areas']['breezecover'] = array(
					'label' => $tools->text('user_settings_name_cover'),
					'icon' => 'administration',
					'file' => \Breeze\Breeze::$folder . 'BreezeUser.php',
					'function' => __NAMESPACE__ .'\BreezeUser::userCoverSettings#',
					'enabled' => $context['user']['is_owner'],
					'permission' => array(
						'own' => 'is_not_guest',
						'any' => 'profile_view',
					),
				);
		}
		// Done with the hacking...
	}

	/**
	 * \Breeze\Breeze::menu()
	 *
	 * Insert a Wall button on the menu buttons array
	 * @param array $menu_buttons An array containing all possible tabs for the main menu.
	 * @link http://mattzuba.com
	 * @return void
	 */
	public function menu(&$menu_buttons)
	{
		global $context, $txt, $scripturl, $user_info;

		// Don't do anything if the mod is off
		if (!$this['tools']->enable('master'))
			return;

		$tools = $this['tools'];
		$userSettings = $this['query']->getUserSettings($user_info['id']);

		// Display the js and css files.
		$this->notiHeaders();

		// Replace the duplicate profile button
		if (!empty($menu_buttons['profile']['sub_buttons']['summary']))
			$menu_buttons['profile']['sub_buttons']['summary'] = array(
				'title' => $txt['summary'],
				'href' => $scripturl . '?action=profile;area=static',
				'show' => true,
			);

		// The Wall link
		$insert = 'home'; // for now lets use the home button as reference...
		$counter = 0;

		foreach ($menu_buttons as $area => $dummy)
			if (++$counter && $area == $insert)
				break;

		$menu_buttons = array_merge(
			array_slice($menu_buttons, 0, $counter),
			array('wall' => array(
				'title' => $tools->text('general_wall'),
				'icon' => 'smiley',
				'href' => $scripturl . '?action=wall',
				'show' => ($tools->enable('master') && !$user_info['is_guest'] && !empty($userSettings['general_wall'])),
				'sub_buttons' => array(
					'noti' => array(
						'title' => $tools->text('user_notisettings_name'),
						'href' => $scripturl . '?action=profile;area=alerts;sa=edit;u='. $user_info['id'],
						'show' => ($tools->enable('master') && !$user_info['is_guest']),
						'sub_buttons' => [],
					),
					'admin' => array(
						'title' => $tools->text('admin'),
						'href' => $scripturl . '?action=admin;area=breezeadmin',
						'show' => ($tools->enable('master') && $user_info['is_admin']),
						'sub_buttons' => [],
					),
				),
			)),
			array_slice($menu_buttons, $counter)
		);
	}

	/**
	 * \Breeze\Breeze::actions()
	 *
	 * Insert the actions needed by this mod
	 * @param array $actions An array containing all possible SMF actions.
	 * @return void
	 */
	public function actions(&$actions)
	{
		// Don't do anything if the mod is off
//		if (!$this['tools']->enable('master'))
//			return;

		// A whole new action just for some ajax calls. Actually, a pretty good chunk of Breeze transactions come through here so...
		$actions['breezeajax'] = [false, '\Breeze\Breeze::call#'];

		// The general wall
		$actions['wall'] = [false, '\Breeze\Breeze::call#'];

		// Replace the buddy action.
		$actions['buddy'] = [false, '\Breeze\Breeze::call#'];

		// Action used when an user wants to change their mood.
		$actions['breezemood'] = [false, '\Breeze\Breeze::call#'];

		// Displaying the users cover/thumbnail.
		$actions['breezecover'] = [false, '\Breeze\Breeze::displayCover#'];

		// proxy
		$actions['breezefeed'] = [false, '\Breeze\Breeze::getFeed#'];
	}

	/**
	 * \Breeze\Breeze::call()
	 *
	 * Wrapper method to call Breeze methods while maintaining dependency injection.
	 * @return void
	 */
	public function call()
	{
		// Just some quick code to make sure this works...
		$action = str_replace('breeze', '', $this->data('get')->get('action'));

		// Don't do anything if the mod is off
		if (!$this['tools']->enable('master') && $action != 'admin')
			return;

		if (!empty($action) && in_array($action, $this->wrapperActions))
			$this[$action]->call();
	}

	/**
	 * \Breeze\Breeze::trackHooks()
	 *
	 * Creates a list of hooks used to track user actions. Should really make sure Breeze is the last hook added.
	 * @return void
	 */
	public function trackHooks()
	{
		// Been the last on the line is cool!
		foreach ($this->trackHooks as $hook => $function)
			add_integration_function($hook, 'BreezeTrackActions::'. $function, false, '$sourcedir/Breeze/BreezeTrackActions.php', true);
	}

	/**
	 * \Breeze\Breeze::displayCover()
	 *
	 * Creates and prints an user cover. If the user does not have a cover it returns false.
	 * @return void
	 */
	public function displayCover()
	{
		global $smcFunc, $modSettings, $maintenance;

		// Get the user ID.
		$useriD = $this->data('get')->get('u');

		// Thumbnail?
		$thumb = $this->data('get')->validate('thumb');

		// Kinda need this!
		if (!$this['tools']->enable('cover') || empty($useriD))
		{
			header('HTTP/1.0 404 File Not Found');
			die('404 File Not Found');
		}

		// Get the user's settings.
		$userSettings = $this['query']->getUserSettings($useriD);

		// Gotta work with paths.
		$folder = $this['tools']->boardDir . \Breeze\Breeze::$coversFolder . $useriD .'/' .($thumb ? 'thumbnail/' : '');

		// False if there is no image.
		$file = empty($userSettings['cover']) ? false : $folder . $userSettings['cover']['basename'];

		// Lots and lots of checks!
		if ((!empty($maintenance) && $maintenance == 2) || empty($file) || !file_exists($file))
		{
			header('HTTP/1.0 404 File Not Found');
			die('404 File Not Found');
		}

		// Kill anything else
		ob_end_clean();

		// This is done to clear any output that was made before now.
		if(!empty($modSettings['enableCompressedOutput']) && !headers_sent() && ob_get_length() == 0)
		{
			if(@ini_get('zlib.output_compression') == '1' || @ini_get('output_handler') == 'ob_gzhandler')
				$modSettings['enableCompressedOutput'] = 0;
			else
				ob_start('ob_gzhandler');
		}

		if(empty($modSettings['enableCompressedOutput']))
		{
			ob_start();
			header('Content-Encoding: none');
		}

		// Get some info.
		$fileTime = filemtime($file);

		// If it hasn't been modified since the last time this attachment was retrieved, there's no need to display it again.
		if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			list($modified_since) = explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
			if (strtotime($modified_since) >= $fileTime)
			{
				ob_end_clean();

				// Answer the question - no, it hasn't been modified ;).
				header('HTTP/1.1 304 Not Modified');
				exit;
			}
		}

		header('Pragma: ');
		header('Expires: '. gmdate('D, d M Y H:i:s', time() + 31536000). ' GMT');
		header('Last-Modified: '. gmdate('D, d M Y H:i:s', $fileTime). ' GMT');
		header('Accept-Ranges: bytes');
		header('Connection: close');
		header('ETag: '. md5($fileTime));
		header('Content-Type: '. $userSettings['cover']['mime']);

		// Since we don't do output compression for files this large...
		if (filesize($file) > 4194304)
		{
			// Forcibly end any output buffering going on.
			while (@ob_get_level() > 0)
				@ob_end_clean();

			$fp = fopen($file, 'rb');
			while (!feof($fp))
			{
				echo fread($fp, 8192);
				flush();
			}
			fclose($fp);
		}

		// On some of the less-bright hosts, readfile() is disabled.  It's just a faster, more byte safe, version of what's in the if.
		elseif (@readfile($file) === null)
			echo file_get_contents($file);

		die();
	}

	/**
	 * \Breeze\Breeze::profilePopUp()
	 *
	 * Adds a few new entries on the pop up menu stuff.
	 * @return void
	 */
	public function profilePopUp(&$profile_items)
	{
		global $user_info, $txt;

		// Can't do much if the master setting is off.
		if (!$this['tools']->enable('master'))
			return;

		$userSettings = $this['query']->getUserSettings($user_info['id']);

		// Gotta replace the Summary link with the static one if the wall is enable.
		if ($this['tools']->enable('force_enable') || !empty($userSettings['wall']))
			foreach ($profile_items as &$item)
				if ($item['area'] == 'summary')
					$item['area'] = 'static';

		// Add a nice link to the user's wall settings page.
		$profile_items[] = array(
			'menu' => 'breeze_profile',
			'area' => 'alerts',
			'url' => $this['tools']->scriptUrl . '?action=profile;area=breezesettings;u='. $user_info['id'],
			'title' => $this['tools']->text('general_my_wall_settings'),
		);
	}

	public function alerts(&$alerts)
	{
		// Don't do anything if the mod is off
		if (!$this['tools']->enable('master'))
			return;

		// Get the results back from BreezeAlerts.
		$this['alerts']->call($alerts);
	}

	public function alertsPref(&$alert_types, &$group_options)
	{
		// Don't do anything if the mod is off
		if (!$this['tools']->enable('master'))
			return;

		// Gonna need some strings.
		$this['tools']->loadLanguage('alerts');

		$alert_types['breeze'] = array(
			''. $this->txtpattern . 'status_owner' => array('alert' => 'yes', 'email' => 'never'),
			''. $this->txtpattern . 'comment_status_owner' => array('alert' => 'yes', 'email' => 'never'),
			''. $this->txtpattern . 'comment_profile_owner' => array('alert' => 'yes', 'email' => 'never'),
			''. $this->txtpattern . 'mention' => array('alert' => 'yes', 'email' => 'never'),
			''. $this->txtpattern . 'like' => array('alert' => 'yes', 'email' => 'never'),
		);
	}

	public function likes($type, $content, $sa, $js, $extra)
	{
		// Don't bother with any other like types.
		if (!$this['tools']->enable('master') || !in_array($type, array_keys($this->likeTypes)))
			return false;

		// Create our returned array
		return array(
			'can_see' => allowedTo('likes_view'),
			'can_like' => allowedTo('likes_like'),
			'type' => $type,
			'flush_cache' => true,
			'callback' => '$sourcedir/Breeze/Breeze.php|\Breeze\Breeze::likesUpdate#',
		);
	}

	public function likesUpdate($object)
	{
		$type = $object->get('type');
		$content = $object->get('content');
		$extra = $object->get('extra');
		$numLikes = $object->get('numLikes');

		// Try and get the user who posted this content.
		$originalAuthor = 0;
		$originalAuthorData = [];
		$row = $this->likeTypes[$type] .'_id';
		$authorColumn = 'poster_id';

		// With the given values, try to fetch the data of the liked content.
		$originalAuthorData = $this['query']->getSingleValue($this->likeTypes[$type], $row, $content);

		if (!empty($originalAuthorData[$authorColumn]))
			$originalAuthor = $originalAuthorData[$authorColumn];

		// Get the userdata.
		$user = $object->get('user');

		// Get the user's options.
		$uOptions = $this['query']->getUserSettings($user['id']);

		// Insert an inner alert if the user wants to and if the data still is there...
		if (!empty($uOptions['alert_like']) && !empty($originalAuthorData))
			$this['query']->createLog(array(
				'member' => $user['id'],
				'content_type' => 'like',
				'content_id' => $content,
				'time' => time(),
				'extra' => array(
					'contentData' => $originalAuthorData,
					'type' => $this->likeTypes[$type],
					'toLoad' => array($user['id'], $originalAuthor),
				),
			));

		// Fire up a notification.
		$this['query']->insertNoti(array(
			'user' => $user['id'],
			'like_type' => $this->likeTypes[$type],
			'content' => $content,
			'numLikes' => $numLikes,
			'extra' => $extra,
			'alreadyLiked' => (bool) $object->get('alreadyLiked'),
			'validLikes' => $object->get('validLikes'),
			'time' => time(),
		), 'like');

		$this['query']->updateLikes($this->likeTypes[$type], $content, $numLikes);
	}

	public function handleLikes($type, $content)
	{
		$data = [];

		// Don't bother with any other like types...
		if (!in_array($type, array_keys($this->likeTypes)))
			return false;

		$row = $this->likeTypes[$type] .'_id';
		$authorColumn = 'poster_id';

		// With the given values, try to find who is the owner of the liked content.
		$data = $this['query']->getSingleValue($this->likeTypes[$type], $row, $content);

		if (!empty($data[$authorColumn]))
			return $data[$authorColumn];

		// Return false if the status/comment is no longer on the DB.
		else
			return false;
	}

	/**
	 * \Breeze\Breeze::notiHeaders()
	 *
	 * Used to embed the JavaScript and other bits of code on every page inside SMF.
	 * @return void
	 */
	public function notiHeaders()
	{
		global $context, $user_info, $settings;

		// Some files are only needed on specific places.
		$action = str_replace('breeze', '', $this->data('get')->get('action'));

		// So, what are we going to do?
		$doAction = in_array($action, $this->wrapperActions) || $action == 'profile';
		$doMood = $this['tools']->enable('mood');

		// Only display these if we are in a "beeze action" or the mood feature is enable.
		if ((!$doAction && !$doMood))
			return false;

		// Always display these.
		loadJavascriptFile('breeze/breeze.js', ['local' => true, 'force_current' => false, 'minimize' => true]);

		// Only needed on certain actions.
		if ($doAction)
		{
			loadCSSFile('breeze.css', ['force_current' => false, 'validate' => true]);
			loadJavascriptFile('breeze/moment.min.js', ['local' => true, 'default_theme' => true, 'defer' => true]);
			loadJavascriptFile('breeze/livestamp.min.js', ['local' => true, 'default_theme' => true, 'defer' => true, 'async' => true]);
		}

		// Up to here for guest.
		if ($user_info['is_guest'])
			return;

		// The main stuff. Always displayed.
		loadJavascriptFile('breeze/noty/jquery.noty.js', ['local' => true, 'default_theme' => true, 'defer' => true, 'async' => true]);
		loadJavascriptFile('breeze/noty/layouts/top.js', ['local' => true, 'default_theme' => true, 'defer' => true, 'async' => true]);
		loadJavascriptFile('breeze/noty/layouts/center.js', ['local' => true, 'default_theme' => true, 'defer' => true, 'async' => true]);
		loadJavascriptFile('breeze/noty/themes/relax.js', ['local' => true, 'default_theme' => true, 'defer' => true, 'async' => true]);

		if (!$doAction)
			return false;

		$tools = $this['tools'];
		$userSettings = $this['query']->getUserSettings($user_info['id']);
		$data = $this->data('get');

		$generalSettings = '';
		$generalText = '';
		$jsSettings = '';

		// Don't pass the "about me" stuff...
		if (!empty($userSettings['aboutMe']))
			unset($userSettings['aboutMe']);

		// Since we're here already, load the current User (currentSettings) object
		foreach (\Breeze\Breeze::$allSettings as $k => $v)
			$generalSettings .= '
	breeze.currentSettings.'. $k .' = '. (isset($userSettings[$k]) ? (is_array($userSettings[$k]) ? json_encode($userSettings[$k]) : JavaScriptEscape($userSettings[$k])) : 'false') .';';

		addInlineJavascript($generalSettings);

		// We still need to pass some text strings to the client.
		$clientText = ['error_empty', 'noti_markasread', 'error_wrong_values', 'noti_delete', 'noti_cancel', 'noti_closeAll', 'noti_checkAll', 'confirm_yes', 'confirm_cancel', 'confirm_delete'];

		foreach ($clientText as $ct)
			$generalText .= '
	breeze.text.'. $ct .' = '. JavaScriptEscape($tools->text($ct)) .';';

		addInlineJavascript($generalText);
	}

	public function mood(&$data, $user, $display_custom_fields)
	{
		// Don't do anything if the feature is disable or custom fields aren't being loaded.
		if (!$this['tools']->enable('master') || !$this['tools']->enable('mood'))
			return;

		// Append the result to the custom fields array.
		$data['custom_fields'][] =  $this['mood']->show($user);
	}

	public function moodProfile($memID, $area)
	{
		// Don't do anything if the mod is off
		if (!$this['tools']->enable('master'))
			return;

		// Let BreezeMood handle this...
		$this['mood']->showProfile($memID, $area);
	}

	// It's all about Admin settings from now on

	/**
	 * \Breeze\Breeze::admin()
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

		$tools->loadLanguage('admin');

		$admin_menu['config']['areas']['breezeadmin'] = array(
			'label' => $tools->text('page_main'),
			'file' => 'Breeze/BreezeAdmin.php',
			'function' => '\Breeze\Breeze::call#',
			'icon' => 'smiley',
			'subsections' => array(
				'general' => array($tools->text('page_main')),
				'settings' => array($tools->text('page_settings')),
				'permissions' => array($tools->text('page_permissions')),
				'cover' => array($tools->text('page_cover')),
				'donate' => array($tools->text('page_donate')),
			),
		);

		// Gotta respect the master mood setting.
		if ($tools->enable('mood'))
		{
			$admin_menu['config']['areas']['breezeadmin']['subsections']['moodList'] = [$tools->text('page_mood')];
			$admin_menu['config']['areas']['breezeadmin']['subsections']['moodEdit'] = [$tools->text('page_mood_create')];
		}
	}

	/**
	 * \Breeze\Breeze::getFeed()
	 *
	 * Proxy function to avoid Cross-origin errors.
	 * @return string
	 */
	public function getFeed()
	{
		global $sourcedir;

		require_once($sourcedir . '/Class-CurlFetchWeb.php');

		$fetch = new \curl_fetch_web_data();
		$fetch->get_url_data(\Breeze\Breeze::$supportSite);

		if ($fetch->result('code') == 200 && !$fetch->result('error'))
			$data = $fetch->result('body');

		else
			return '';

		smf_serverResponse($data, 'Content-type: text/xml');
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
						'site' => 'https://missallsunday.com',
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
					'noty' => array(
						'name' => 'noty jquery plugin',
						'site' => 'http://needim.github.com/noty/',
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
					'metro' => array(
						'name' => 'Font Awesome',
						'site' => 'http://fortawesome.github.io/Font-Awesome/',
					),
					'skype' => array(
						'name' => 'skype icons',
						'site' => 'http://blogs.skype.com/2006/09/01/icons-and-strings',
					),
				),
			),
		);

		// Oh well, one can dream...
		call_integration_hook('integrate_breeze_credits', [&$credits]);

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
