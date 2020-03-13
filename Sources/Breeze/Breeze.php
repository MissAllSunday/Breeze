<?php

declare(strict_types=1);

namespace Breeze;

use Breeze\Config\MapperAggregate;
use Breeze\Controller\AdminController;
use Breeze\Controller\Buddy;
use Breeze\Controller\Comment;
use Breeze\Controller\Cover;
use Breeze\Controller\Mood;
use Breeze\Controller\Status;
use Breeze\Controller\User\Wall;
use Breeze\Repository\Like\Base as LikeRepository;
use Breeze\Repository\Like\Comment as LikeCommentRepository;
use Breeze\Repository\Like\Status as LikeStatusRepository;
use Breeze\Service\MoodService;
use Breeze\Service\Permissions;
use Breeze\Service\PermissionsService;
use Breeze\Service\UserService;
use Breeze\Traits\TextTrait;
use League\Container\Container as Container;

if (!defined('SMF'))
	die('No direct access...');

class Breeze
{
	use TextTrait;

	public const NAME = 'Breeze';
	public const VERSION = '2.0';
	public const PATTERN = self::NAME . '_';
	public const FEED = '//github.com/MissAllSunday/Breeze/releases.atom';

	/**
	 * @var Container
	 */
	protected $container;

	public function __construct()
	{
		$this->container = new Container();
		$mappers = (new MapperAggregate())->getMappers();

		foreach ($mappers as $mapperFile)
			foreach ($mapperFile as $mapperAlias => $mapperInfo)
			{
				if (empty($mapperInfo['class']))
					continue;

				if (!empty($mapperInfo['arguments']))
					$this->container->add($mapperInfo['class'])->addArguments($mapperInfo['arguments']);

				else
					$this->container->add($mapperInfo['class']);
			}
	}

	public function permissionsWrapper(&$permissionGroups, &$permissionList): void
	{
		$this->container->get(PermissionsService::class)->hookPermissions($permissionGroups, $permissionList);
	}

	public function profileMenuWrapper(&$profile_areas): void
	{
		$this->container->get(UserService::class)->hookProfileMenu($profile_areas);
	}

	public function menu(&$menu_buttons): void
	{
		//if (!$this->enable('master'))
			return;

		$scriptUrl = $this->global('scripturl');
		$currentUserInfo = $this->global('user_info');
		$currentUserSettings = $this->container->get(UserService::class)->getCurrentUserSettings();

		if (!empty($menu_buttons['profile']['sub_buttons']['summary']))
			$menu_buttons['profile']['sub_buttons']['summary'] = [
				'title' => $this->getText('summary'),
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
				'title' => $this->getText('general_wall'),
				'icon' => 'smiley',
				'href' => $scriptUrl . '?action=wall',
				'show' => ($this->enable('master') &&
					!$currentUserInfo['is_guest'] &&
					!empty($currentUserSettings['general_wall'])),
				'sub_buttons' => [
					'noti' => [
						'title' => $this->getText('user_notisettings_name'),
						'href' => $scriptUrl . '?action=profile;area=alerts;sa=edit;u=' . $currentUserInfo['id'],
						'show' => ($this->enable('master') && !$currentUserInfo['is_guest']),
						'sub_buttons' => [],
					],
					'admin' => [
						'title' => $this->getText('admin'),
						'href' => $scriptUrl . '?action=admin;area=breezeadmin',
						'show' => ($this->enable('master') && $currentUserInfo['is_admin']),
						'sub_buttons' => [],
					],
				],
			]],
			array_slice($menu_buttons, $counter)
		);
	}

	public function actions(&$actions): void
	{
		$actions['breezeFeed'] = [false, Feed::class . '::do#'];

		//if (!$this->enable('master'))
			return;

		$actions['breezeStatus'] = [false,  Status::class . '::do#'];
		$actions['breezeComment'] = [false, Comment::class . '::do#'];
		$actions['breezeWall'] = [false, Wall::class . '::do#'];
		$actions['breezeBuddy'] = [false, Buddy::class . '::do#'];
		$actions['breezeMood'] = [false, Mood::class . '::do#'];
		$actions['breezeCover'] = [false, Cover::class . '::do#'];
	}

	public function profilePopUpWrapper(&$profile_items): void
	{
		$this->container->get(UserService::class)->hookProfilePopUp($profile_items);
	}

	public function alertsPrefWrapper( array &$alertTypes, &$groupOptions): void
	{
		$this->container->get(UserService::class)->hookAlertsPref($alertTypes);
	}

	public function updateLikesWrapper($type, $content, $sa, $js, $extra)
	{
		if (!$this->enable('master') || !in_array($type, LikeRepository::getAllTypes()))
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
			'callback' => $likes->update(),
		];
	}

	public function displayMoodWrapper( array &$data, int $userId, $displayCustomFields): void
	{
		/** @var MoodService */
		$moodService = $this->container->get(MoodService::class);

		$moodService->displayMood($data, $userId);
	}

	public function displayMoodProfileWrapper($memID, $area): void
	{
		// Don't do anything if the mod is off
		if (!$this->enable('master'))
			return;

		// Let BreezeMood handle this...
		$this['mood']->showProfile($memID, $area);
	}

	public function adminMenuWrapper(array &$adminMenu): void
	{
		/** @var AdminController */
		$adminController = $this->container->get(AdminController::class);

		$this->setLanguage('BreezeAdmin');

		$adminMenu['config']['areas']['breezeAdmin'] = [
			'label' => $this->getText('page_main'),
			'function' => [$adminController, 'dispatch'],
			'icon' => 'smiley',
			'subsections' => [
				'general' => [$this->getText('page_main')],
				'settings' => [$this->getText('page_settings_title')],
				'permissions' => [$this->getText('page_permissions_title')],
				'cover' => [$this->getText('page_cover_title')],
				'donate' => [$this->getText('page_donate_title')],
			],
		];

		if ($this->enable('mood'))
		{
			$admin_menu['config']['areas']['breezeAdmin']['subsections']['moodList'] = [
				$this->getText('page_mood')
			];
			$admin_menu['config']['areas']['breezeAdmin']['subsections']['moodEdit'] = [
				$this->getText('page_mood_create')
			];
		}
	}

	public static function credits(): array
	{
		return [
			'dev' => [
				'name' => 'Developer(s)',
				'users' => [
					'suki' => [
						'name' => 'Jessica "Suki" Gonz&aacute;lez',
						'site' => '//missallsunday.com',
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
