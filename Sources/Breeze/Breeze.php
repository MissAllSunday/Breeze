<?php

declare(strict_types=1);

namespace Breeze;

use Breeze\Controller\Buddy;
use Breeze\Controller\Comment;
use Breeze\Controller\Cover;
use Breeze\Controller\Mood;
use Breeze\Controller\Status;
use Breeze\Controller\User\Wall;
use Breeze\Repository\Like\Base as LikeRepository;
use Breeze\Repository\Like\Comment as LikeCommentRepository;
use Breeze\Repository\Like\Status as LikeStatusRepository;
use Breeze\Service\Permissions;
use Breeze\Service\Settings;
use Breeze\Service\Text;
use Breeze\Service\User as UserService;
use League\Container\Container as Container;
use League\Container\ReflectionContainer as ReflectionContainer;

if (!defined('SMF'))
	die('No direct access...');

class Breeze
{
	public const NAME = 'Breeze';
	public const VERSION = '1.1';
	public const PATTERN = self::NAME . '_';
	public const FEED = '//github.com/MissAllSunday/Breeze/releases.atom';

	/**
	 * @var Settings
	 */
	protected $settings;

	/**
	 * @var Text
	 */
	protected $text;

	/**
	 * @var Container
	 */
	protected $container;

	public function __construct()
	{
		$this->container = new Container;

		$this->container->delegate(
		    new ReflectionContainer
		);

		$this->settings = $this->container->get(Settings::class);
		$this->text = $this->container->get(Text::class);
	}

	public function permissions(&$permissionGroups, &$permissionList): void
	{
		$this->container->get(Permissions::class)->hookPermissions($permissionGroups, $permissionList);
	}

	public function profile(&$profile_areas): void
	{
		$this->container->get(UserService::class)->hookProfileMenu($profile_areas);
	}

	public function menu(&$menu_buttons): void
	{
		if (!$this->settings->enable('master'))
			return;

		$scriptUrl = $this->settings->global('scripturl');
		$currentUserInfo = $this->settings->global('user_info');
		$currentUserSettings = $this->container->get(UserService::class)->getCurrentUserSettings();

		if (!empty($menu_buttons['profile']['sub_buttons']['summary']))
			$menu_buttons['profile']['sub_buttons']['summary'] = [
			    'title' => $this->text->get('summary'),
			    'href' => $scriptUrl . '?action=profile;area=static',
			    'show' => true,
			];

		$menuReference = 'home';
		$counter = 0;

		foreach ($menu_buttons as $area => $dummy)
			if (++$counter && $area == $menuReference)
				break;

		$menu_buttons = array_merge(
		    array_slice($menu_buttons, 0, $counter),
		    ['wall' => [
		        'title' => $this->text->get('general_wall'),
		        'icon' => 'smiley',
		        'href' => $scriptUrl . '?action=wall',
		        'show' => ($this->settings->enable('master') &&
					!$currentUserInfo['is_guest'] &&
					!empty($currentUserSettings['general_wall'])),
		        'sub_buttons' => [
		            'noti' => [
		                'title' => $this->text->get('user_notisettings_name'),
		                'href' => $scriptUrl . '?action=profile;area=alerts;sa=edit;u=' . $currentUserInfo['id'],
		                'show' => ($this->settings->enable('master') && !$currentUserInfo['is_guest']),
		                'sub_buttons' => [],
		            ],
		            'admin' => [
		                'title' => $this->text->get('admin'),
		                'href' => $scriptUrl . '?action=admin;area=breezeadmin',
		                'show' => ($this->settings->enable('master') && $currentUserInfo['is_admin']),
		                'sub_buttons' => [],
		            ],
		        ],
		    ]],
		    array_slice($menu_buttons, $counter)
		);
	}

	public function actions(&$actions): void
	{
		// proxy, allow this action even if the master setting is off
		$actions['breezefeed'] = [false, '\Breeze\Breeze::getFeed#'];

		// Don't do anything if the mod is off
		if (!$this['tools']->enable('master'))
			return;

		$actions['breezeStatus'] = [false,  Status::class . '::do#'];
		$actions['breezeComment'] = [false, Comment::class . '::do#'];
		$actions['breezeWall'] = [false, Wall::class . '::do#'];
		$actions['breezeBuddy'] = [false, Buddy::class . '::do#'];
		$actions['breezeMood'] = [false, Mood::class . '::do#'];
		$actions['breezeCover'] = [false, Cover::class . '::do#'];
	}

	public function profilePopUp(&$profile_items): void
	{
		$this->container->get(UserService::class)->hookProfilePopUp($profile_items);
	}

	public function alertsPref(&$alert_types, &$group_options): void
	{
		$this->container->get(UserService::class)->hookAlertsPref($alert_types);
	}

	public function likes($type, $content, $sa, $js, $extra)
	{
		if (!$this->settings->enable('master') || !in_array($type, LikeRepository::getAllTypes()))
			return false;

		switch ($type)
		{
			case LikeStatusRepository::LIKE_TYPE_STATUS:
				$likes = $this->container->get(LikeStatusRepository::class);
			case LikeCommentRepository::LIKE_TYPE_COMMENT:
				$likes = $this->container->get(LikeCommentRepository::class);
		}

		$permissions = $this->container->get(Permissions::class);

		return [
		    'can_see' => $permissions->get('likes_view'),
		    'can_like' => $permissions->get('likes_like'),
		    'type' => $type,
		    'flush_cache' => true,
		    'callback' => $likes->likesUpdate(),
		];
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
		$doAction = in_array($action, $this->wrapperActions) || 'profile' == $action;
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
			loadJavascriptFile('breeze/moment.min.js', ['local' => true, 'default_theme' => true, 'defer' => true, 'async' => true]);
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
	breeze.currentSettings.' . $k . ' = ' . (isset($userSettings[$k]) ? (is_array($userSettings[$k]) ? json_encode($userSettings[$k]) : JavaScriptEscape($userSettings[$k])) : 'false') . ';';

		addInlineJavascript($generalSettings);

		// We still need to pass some text strings to the client.
		$clientText = ['error_empty', 'noti_markasread', 'error_wrong_values', 'noti_delete', 'noti_cancel', 'noti_closeAll', 'noti_checkAll', 'confirm_yes', 'confirm_cancel', 'confirm_delete'];

		foreach ($clientText as $ct)
			$generalText .= '
	breeze.text.' . $ct . ' = ' . JavaScriptEscape($tools->text($ct)) . ';';

		addInlineJavascript($generalText);
	}

	public function mood(&$data, $user, $display_custom_fields): void
	{
		// Don't do anything if the feature is disable or custom fields aren't being loaded.
		if (!$this['tools']->enable('master') || !$this['tools']->enable('mood'))
			return;

		// Append the result to the custom fields array.
		$data['custom_fields'][] =  $this['mood']->show($user);
	}

	public function moodProfile($memID, $area): void
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
	 */
	public function admin(&$admin_menu): void
	{
		global $breezeController;

		$tools = $this['tools'];

		$tools->loadLanguage('admin');

		$admin_menu['config']['areas']['breezeadmin'] = [
		    'label' => $tools->text('page_main'),
		    'file' => 'Breeze/BreezeAdmin.php',
		    'function' => '\Breeze\Breeze::call#',
		    'icon' => 'smiley',
		    'subsections' => [
		        'general' => [$tools->text('page_main')],
		        'settings' => [$tools->text('page_settings')],
		        'permissions' => [$tools->text('page_permissions')],
		        'cover' => [$tools->text('page_cover')],
		        'donate' => [$tools->text('page_donate')],
		    ],
		];

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

		if (200 == $fetch->result('code') && !$fetch->result('error'))
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
		$credits = [
		    'dev' => [
		        'name' => 'Developer(s)',
		        'users' => [
		            'suki' => [
		                'name' => 'Jessica "Suki" Gonz&aacute;lez',
		                'site' => 'https://missallsunday.com',
		            ],
		        ],
		    ],
		    'scripts' => [
		        'name' => 'Third Party Scripts',
		        'users' => [
		            'jQuery' => [
		                'name' => 'jQuery',
		                'site' => 'http://jquery.com/',
		            ],
		            'noty' => [
		                'name' => 'noty jquery plugin',
		                'site' => 'http://needim.github.com/noty/',
		            ],
		            'moment' => [
		                'name' => 'moment.js',
		                'site' => 'http://momentjs.com/',
		            ],
		            'livestamp' => [
		                'name' => 'Livestamp.js',
		                'site' => 'http://mattbradley.github.io/livestampjs/',
		            ],
		            'fileUpload' => [
		                'name' => 'jQuery File Upload Plugin',
		                'site' => 'https://github.com/blueimp/jQuery-File-Upload',
		            ],
		        ],
		    ],
		    'images' => [
		        'name' => 'Icons',
		        'users' => [
		            'metro' => [
		                'name' => 'Font Awesome',
		                'site' => 'http://fortawesome.github.io/Font-Awesome/',
		            ],
		            'skype' => [
		                'name' => 'skype icons',
		                'site' => 'http://blogs.skype.com/2006/09/01/icons-and-strings',
		            ],
		        ],
		    ],
		];

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
