<?php

declare(strict_types=1);

/**
 * BreezeTools
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2019, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

namespace Breeze;

if (!defined('SMF'))
	die('No direct access...');

class BreezeTools
{
	public $_pattern;
	protected $_app;
	public $sourceDir;
	public $scriptUrl;
	public $settings;
	public $boardDir;
	public $boardUrl;
	static $_users = [];

	function __construct(Breeze $app)
	{
		global $sourcedir, $scripturl, $boardurl;
		global $settings, $boarddir;

		$this->_pattern = Breeze::NAME . '_';
		$this->_app = $app;
		$this->sourceDir = $sourcedir;
		$this->scriptUrl = $scripturl;
		$this->settings = $settings;
		$this->boardDir = $boarddir;
		$this->boardUrl = $boardurl;

		// Load the mod's language file.
		loadLanguage(Breeze::NAME);
	}

	public function loadLanguage($type)
	{
		if (empty($type))
			return;

		// Load the mod's language file.
		loadLanguage(Breeze::NAME . ucfirst($type));
	}

	/**
	 * BreezeTools::text()
	 *
	 * Gets a string key, and returns the associated text string.
	 * @param string $var The text string key.
	 * @global $txt
	 * @return string|boolean
	 */
	public function text($var)
	{
		global $txt;

		if (empty($var))
			return false;

		if (!empty($txt[$this->_pattern . $var]))
			return $txt[$this->_pattern . $var];

		
			return false;
	}

	/**
	 * BreezeTools::enable()
	 *
	 * Gets a name and checks if the appropriated settings does exists, returns false otherwise.
	 * @param string $var the setting's name
	 * @global $modSettings
	 * @return boolean
	 */
	public function enable($var)
	{
		global $modSettings;

		if (empty($var))
			return false;

		if (isset($modSettings[$this->_pattern . $var]) && !empty($modSettings[$this->_pattern . $var]))
			return true;

		
			return false;
	}

	/**
	 * BreezeTools::setting()
	 *
	 * returns the requested setting.
	 * @param string $var the setting's name
	 * @return string|boolean
	 */
	public function setting($var)
	{
		global $modSettings;

		if (empty($var))
			return false;

		global $modSettings;

		if ($this->enable($var) == true)
			return $modSettings[$this->_pattern . $var];

		
			return false;
	}

	public function modSettings($var)
	{
		global $modSettings;

		if (empty($var))
			return false;

		if (isset($modSettings[$var]))
			return $modSettings[$var];

		
			return false;
	}

	/**
	 * BreezeTools::timeElapsed()
	 *
	 * Gets an unix timestamp and returns a relative date from the current time.
	 * @param integer $ptime An unix timestamp
	 * @link http://www.zachstronaut.com/posts/2009/01/20/php-relative-date-time-string.html
	 * @return string
	 */
	public function timeElapsed($ptime)
	{
		$etime = time() - $ptime;

		if ($etime < 1)
			return $this->text('time_just_now');

		$a = [
		    12 * 30 * 24 * 60 * 60	=> $this->text('time_year'),
		    30 * 24 * 60 * 60		=> $this->text('time_month'),
		    24 * 60 * 60			=> $this->text('time_day'),
		    60 * 60					=> $this->text('time_hour'),
		    60						=> $this->text('time_minute'),
		    1						=> $this->text('time_second')
		];

		foreach ($a as $secs => $str)
		{
			$d = $etime / $secs;
			if ($d >= 1)
			{
				$r = round($d);
				return $r . ' ' . $str . ($r > 1 ? 's ' : ' ') . $this->text('time_ago');
			}
		}
	}

	/**
	 * BreezeTools::isJson()
	 *
	 * Checks if a given string is a json string
	 * @param string $string a text to check
	 * @return boolean
	 */
	public function isJson($string)
	{
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	// A function to cut-off a string
	public function truncateString($string, $limit, $break = ' ', $pad = '...')
	{
		if(empty($string))
			return false;

		if(empty($limit))
			$limit = 30;

		 // return with no change if string is shorter than $limit
		if(strlen($string) <= $limit)
			return $string;

		// is $break present between $limit and the end of the string?
		if(($breakpoint = strpos($string, $break, $limit)) !== false)
			if($breakpoint < strlen($string) - 1)
				$string = substr($string, 0, $breakpoint) . $pad;

		return $string;
	}

	/**
	 * BreezeTools::returnKey()
	 *
	 * Checks if a value on a multidimensional array exists and return the main key
	 * @param string $value The value to check
	 * @param array $array The array to check against.
	 * @return string|boolean returns the key if it does exists.
	 */
	public function returnKey($value, $array)
	{
		if (empty($value) || empty($array))
			return false;

		foreach ($array as $k => $v)
		{
			if (is_array($v))
			{
				if (in_array($value, $v))
					return $k;

					
						return false;
			}

			
			
				if ($v == $value)
					return $k;

				
					return false;
			
		}
	}

	/**
	 * BreezeTools::remove()
	 *
	 * Removes a key from a multidimensional array.
	 * @param array $array The array to remove the value from.
	 * @param string|boolean  the value to remove.
	 * @param boolean $preserve_keys
	 * @return array The modified array.
	 */
	public function remove($array, $val, $preserve_keys = true)
	{
		if (empty($array) || empty($val) || !is_array($array))
			return false;

		if (!is_array($val))
		{
			if (!in_array($val, $array))
				return $array;

			foreach($array as $key => $value)
			{
				if ($value == $val)
					unset($array[$key]);
			}
		}

		elseif (is_array($val))
		{
			foreach($val as $find)
				foreach($array as $key => $value)
				{
					if (empty($array) || !is_array($array))
						return false;

					if ($value == $find)
						unset($array[$key]);
				}
		}

		else
			return false;

		return ($preserve_keys === true) ? $array : array_values($array);
	}

	/**
	 * BreezeTools::loadUserInfo()
	 *
	 * Loads the specified user or users information.
	 * @param integer|array $id user(s) unique ID.
	 * @param boolean $returnID return the loaded ID.
	 * @return integer the loaded IDs
	 */
	public function loadUserInfo($id, $returnID = false)
	{
		global $context, $memberContext, $txt;

		// If this isn't an array, lets change it to one.
		$id = (array) $id;
		$id = array_unique($id);

		// Only load those that haven't been loaded yet.
		if (!empty(static::$_users))
			foreach ($id as $k => $v)
				if (!empty(static::$_users[$v]))
					unset($id[$k]);

		// Got nothing to load.
		if (empty($id))
		{
			$context['Breeze']['user_info'] = static::$_users;
			return $returnID ? array_keys(static::$_users) : false;
		}

		// $memberContext gets set and globalized, we're gonna take advantage of it
		$loadedIDs = loadMemberData($id, false, 'profile');

		// Set the context var.
		foreach ($id as $k => $u)
		{
			// Set an empty array.
			static::$_users[$u] = [
			    'breezeFacebox' => '',
			    'link' => '',
			    'name' => '',
			    'linkFacebox' => '',
			];

			// Gotta make sure you're only loading info from real existing members...
			if (is_array($loadedIDs) && in_array($u, $loadedIDs))
			{
				loadMemberContext($u, true);

				$user = $memberContext[$u];

				// Pass the entire data array.
				static::$_users[$user['id']] = $user;

				// Build the "breezeFacebox" link. Rename "facebox" to "breezeFacebox" in case there are other mods out there using facebox, specially its a[rel*=facebox] stuff.
				static::$_users[$user['id']]['breezeFacebox'] = '<a href="' . $this->scriptUrl . '?action=wall;sa=userdiv;u=' . $u . '" class="avatar" rel="breezeFacebox" data-name="' . (!empty($user['name']) ? $user['name'] : '') . '">' . $user['avatar']['image'] . '</a>';

				// Also provide a no avatar facebox link.
				static::$_users[$user['id']]['linkFacebox'] = '<a href="' . $this->scriptUrl . '?action=wall;sa=userdiv;u=' . $u . '" class="avatar" rel="breezeFacebox" data-name="' . (!empty($user['name']) ? $user['name'] : '') . '">' . $user['name'] . '</a>';
			}

			// Not a real member, fill out some guest generic vars and be done with it..
			else
				static::$_users[$u] = [
				    'breezeFacebox' => $txt['guest_title'],
				    'link' => $txt['guest_title'],
				    'name' => $txt['guest_title'],
				    'linkFacebox' => $txt['guest_title'],
				];
		}

		$context['Breeze']['user_info'] = static::$_users;

		// Lastly, if the ID was requested, sent it back!
		if ($returnID)
			return $loadedIDs;
	}

    /**
     * BreezeTools::permissions()
     *
     * Handles status/comments related permissions, it does it on a case per case basics.
     * @param string $type Either a comment or a status.
     * @param bool $profileOwner the profile where this status/comment was posted.
     * @param bool $userPoster The person who posted this status/comment
     * @return array all possible permissions as integer values. 0 can't, 1 can.
     */
	public function permissions($type, $profileOwner = false, $userPoster = false)
	{
		global $user_info;

		// Add this bit here to make it easier in the future to add more permissions.
		$perm = [
		    'edit' => false,
		    'delete' => '',
		    'post' => false,
		    'postComments' => false,
		];

		// NO! you don't have permission to do nothing...
		if ($user_info['is_guest'] || !$userPoster || !$profileOwner || empty($type))
			return $perm;

		// Profile owner?
		$isProfileOwner = $profileOwner == $user_info['id'];

		// Status owner?
		$isPosterOwner = $userPoster == $user_info['id'];

		// Lets check the posing bit first. Profile owner can always post.
		if ($isProfileOwner)
		{
			$perm['post'] = true;
			$perm['postComments'] = true;
		}

		else
		{
			$perm['post'] = allowedTo('breeze_post' . $type);
			$perm['postComments'] = allowedTo('breeze_postComments');
		}

		// It all starts with an empty vessel...
		$allowed = [];

		// Your own data?
		if ($isPosterOwner && allowedTo('breeze_deleteOwn' . $type))
			$allowed[] = 1;

		// Nope? then is this your own profile?
		if ($isProfileOwner && allowedTo('breeze_deleteProfile' . $type))
			$allowed[] = 1;

		// No poster and no profile owner, must be an admin/mod or something.
		if (allowedTo('breeze_delete' . $type))
			$allowed[] = 1;

		$perm['delete'] = in_array(1, $allowed);

		return $perm;
	}

	public function setResponse($message, $type)
	{
		if (empty($message) || empty($type))
			return;

		// Yeah, a nice session var...
		$_SESSION['Breeze']['response'] = [
		    'message' => $message,
		    'type' => $type,
		];
	}

	public function getResponse()
	{
		if (empty($_SESSION['Breeze']['response']))
			return false;

		$response = $_SESSION['Breeze']['response'];
		unset($_SESSION['Breeze']['response']);

		return $response;
	}

	public function deleteCover($image, $user)
	{
		if (empty($image) || empty($user))
			return;

		// This makes things easier.
		$folder = $this->boardDir . Breeze::$coversFolder . $user . '/';
		$folderThumbnail = $this->boardDir . Breeze::$coversFolder . $user . '/thumbnail/';

		if (file_exists($folderThumbnail . $image))
			@unlink($folderThumbnail . $image);

		if (file_exists($folder . $image))
			@unlink($folder . $image);
	}

	public function parser($text, $replacements = [])
	{
		global $context;

		if (empty($text) || empty($replacements) || !is_array($replacements))
			return '';

		// Inject the session.
		$s = ';' . $context['session_var'] . '=' . $context['session_id'];

		// Split the replacements up into two arrays, for use with str_replace.
		$find = [];
		$replace = [];

		foreach ($replacements as $f => $r)
		{
			$find[] = '{' . $f . '}';
			$replace[] = $r . ((strpos($f, 'href') !== false) ? $s : '');
		}

		// Do the variable replacements.
		return str_replace($find, $replace, $text);
	}

	public function formatBytes($bytes, $showUnits = false)
	{
		$units = ['B', 'KB', 'MB', 'GB', 'TB'];

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= (1 << (10 * $pow));

		return round($bytes, 4) . ($showUnits ? ' ' . $units[$pow] : '');
	}

	/**
	 * BreezeTools::stalkingCheck()
	 *
	 * @param integer $fetchedUser the user to check against.
	 * Checks if the current user has been added in someone's ignored list.
	 * @return boolean
	 */
	public function stalkingCheck($fetchedUser = 0)
	{
		global $user_info;

		// But of course you can stalk non-existent users!
		if (empty($fetchedUser))
			return true;

		// Get the "stalkee" user settings.
		$stalkedSettings = $this->_app['query']->getUserSettings($fetchedUser);

		// Check if the stalker has been added in stalkee's ignore list.
		if (!empty($stalkedSettings['kick_ignored']) && !empty($stalkedSettings['ignoredList']))
		{
			$ignored = explode(',', $stalkedSettings['ignoredList']);

			return in_array($user_info['id'], $ignored);
		}

		// Lucky you!
		
			return false;
	}

	/**
	 * BreezeTools::floodControl()
	 *
	 * @param integer $user Thew user ID to check.
	 * Checks if the current user has not surpassed the amount of times an user can post per minute.
	 * @return boolean  True if the user can post, false otherwise ORLY?
	 */
	public function floodControl($user = 0)
	{
		global $user_info;

		// No param? use the current user then.
		$user = !empty($user) ? $user : $user_info['id'];

		// Set some needed stuff.
		$seconds = 60 * ($this->setting('flood_minutes') ? $this->setting('flood_minutes') : 5);
		$messages = $this->setting('flood_messages') ? $this->setting('flood_messages') : 10;

		// Has it been defined yet?
		if (!isset($_SESSION['Breeze_floodControl' . $user]))
			$_SESSION['Breeze_floodControl' . $user] = [
			    'time' => time() + $seconds,
			    'msg' => 0,
			];

		// Keep track of it.
		$_SESSION['Breeze_floodControl' . $user]['msg']++;

		// Short name.
		$flood = $_SESSION['Breeze_floodControl' . $user];

		// Chatty one huh?
		if ($flood['msg'] >= $messages && time() <= $flood['time'])
			return false;

		// Enough time has passed, give the user some rest.
		if (time() >= $flood['time'])
			unset($_SESSION['Breeze_floodControl' . $user]);

		return true;
	}

	public function commaSeparated($string, $type = 'alphanumeric')
	{
		switch ($type) {
			case 'numeric':
				$t = '\d';
				break;
			case 'alpha':
				$t = '[:alpha:]';
				break;
			case 'alphanumeric':
			default:
				$t = '[:alnum:]';
				break;
		}

		return empty($string) ? false : implode(',', array_filter(explode(',', preg_replace(
		    [
		        '/[^' . $t . ',]/',
		        '/(?<=,),+/',
		        '/^,+/',
		        '/,+$/'
		    ],
		    '',
		    $string
		))));
	}
}
