<?php

declare(strict_types=1);

namespace Breeze;

use Breeze\Config\MapperAggregate;
use Breeze\Controller\AdminController;
use Breeze\Controller\API\CommentController;
use Breeze\Controller\API\MoodController;
use Breeze\Controller\API\StatusController;
use Breeze\Controller\BuddyController;
use Breeze\Controller\User\Settings\AlertsController;
use Breeze\Controller\User\Settings\UserSettingsController;
use Breeze\Controller\User\WallController;
use Breeze\Entity\SettingsEntity;
use Breeze\Service\Actions\AdminService;
use Breeze\Service\Actions\UserSettingsService;
use Breeze\Service\MoodService;
use Breeze\Service\PermissionsService;
use Breeze\Service\UserService;
use Breeze\Traits\TextTrait;
use League\Container\Container as Container;

class Breeze
{
	use TextTrait;

	public const NAME = 'Breeze';
	public const VERSION = '2.0';
	public const PATTERN = self::NAME . '_';
	public const FEED = 'https://api.github.com/repos/MissAllSunday/Breeze/releases';
	public const SUPPORT_URL = 'https://missallsunday.com';
	public const VUE_VERSION = '2.5.16';

	protected Container $container;

	public function __construct()
	{
		$this->container = new Container();
		$mappers = (new MapperAggregate())->getMappers();

		foreach ($mappers as $mapperFile) {
			foreach ($mapperFile as $mapperAlias => $mapperInfo) {
				if (empty($mapperInfo['class'])) {
					continue;
				}

				if (!empty($mapperInfo['arguments'])) {
					$this->container->add($mapperInfo['class'])->addArguments($mapperInfo['arguments']);
				} else {
					$this->container->add($mapperInfo['class']);
				}
			}
		}
	}

	public function permissionsWrapper(array &$permissionGroups, array &$permissionList): void
	{
		$this->container->get(PermissionsService::class)->hookPermissions($permissionGroups, $permissionList);
	}

	public function profileMenuWrapper(array &$profileAreas): void
	{
		if (!$this->isEnable(SettingsEntity::MASTER)) {
			return;
		}

		$this->setLanguage(Breeze::NAME);
		$context = $this->global('context');
		$currentUserSettings = $this->container->get(UserService::class)->getCurrentUserSettings();

		if ($this->isEnable(SettingsEntity::FORCE_WALL) || !empty($currentUserSettings['wall'])) {
			/** @var WallController */
			$wallController = $this->container->get(WallController::class);

			$profileAreas['info']['areas']['summary'] = [
				'label' => $this->getText('general_wall'),
				'icon' => 'smiley',
				'function' => [$wallController, 'dispatch'],
				'permission' => [
					'own' => 'is_not_guest',
					'any' => 'profile_view',
				],
			];

			$profileAreas['info']['areas'][UserService::LEGACY_AREA] = [
				'label' => $this->getText('general_summary'),
				'icon' => 'members',
				'file' => 'Profile-View.php',
				'function' => 'summary',
				'permission' => [
					'own' => 'is_not_guest',
					'any' => 'profile_view',
				],
			];
		}

		/** @var UserSettingsController */
		$settingsController = $this->container->get(UserSettingsController::class);

		/** @var AlertsController */
		$alertsController = $this->container->get(AlertsController::class);

		$profileAreas['breeze_profile'] = [
			'title' => $this->getText('general_my_wall_settings'),
			'areas' => [],
		];

		$profileAreas['breeze_profile']['areas']['breezeSettings'] = [
			'label' => $this->getText(UserSettingsService::AREA . '_main_title'),
			'icon' => 'maintain',
			'function' => [$settingsController, 'dispatch'],
			'enabled' => $context['user']['is_owner'],
			'permission' => [
				'own' => 'is_not_guest',
				'any' => 'profile_view',
			],
		];

		$profileAreas['breeze_profile']['areas']['breezeAlerts'] = [
			'label' => $this->getText('user_settings_name_alerts'),
			'function' => [$alertsController, 'dispatch'],
			'enabled' => $context['user']['is_owner'],
			'icon' => 'maintain',
			'subsections' => [
				'settings' => [
					$this->getText('user_settings_name_alerts_settings'),
					['is_not_guest', 'profile_view']],
				'edit' => [
					$this->getText('user_settings_name_alerts_edit'),
					['is_not_guest', 'profile_view']],
			],
			'permission' => [
				'own' => 'is_not_guest',
				'any' => 'profile_view',
			],
		];
	}

	public function menu(&$menu_buttons): void
	{
		if (!$this->isEnable(SettingsEntity::MASTER)) {
			return;
		}

		$scriptUrl = $this->global('scripturl');
		$currentUserInfo = $this->global('user_info');
		$currentUserSettings = $this->container->get(UserService::class)->getCurrentUserSettings();

		if (!empty($menu_buttons['profile']['sub_buttons']['summary'])) {
			$menu_buttons['profile']['sub_buttons']['summary'] = [
				'title' => $this->getText('summary'),
				'href' => $scriptUrl . '?action=profile;area=' . UserService::LEGACY_AREA,
				'show' => true,
			];
		}

		$menuReference = 'home';
		$counter = 0;

		foreach ($menu_buttons as $area => $dummy) {
			if (++$counter && $area == $menuReference) {
				break;
			}
		}

		$menu_buttons = array_merge(
			array_slice($menu_buttons, 0, $counter),
			['wall' => [
				'title' => $this->getText('general_wall'),
				'icon' => 'smiley',
				'href' => $scriptUrl . '?action=wall',
				'show' => ($this->isEnable(SettingsEntity::MASTER) &&
					!$currentUserInfo['is_guest'] &&
					!empty($currentUserSettings['general_wall'])),
				'sub_buttons' => [
					'noti' => [
						'title' => $this->getText('user_notisettings_name'),
						'href' => $scriptUrl . '?action=profile;area=alerts;sa=edit;u=' . $currentUserInfo['id'],
						'show' => ($this->isEnable(SettingsEntity::MASTER) && !$currentUserInfo['is_guest']),
						'sub_buttons' => [],
					],
					'admin' => [
						'title' => $this->getText('admin'),
						'href' => $scriptUrl . '?action=admin;area=' . AdminService::AREA,
						'show' => ($this->isEnable(SettingsEntity::MASTER) && $currentUserInfo['is_admin']),
						'sub_buttons' => [],
					],
				],
			]],
			array_slice($menu_buttons, $counter)
		);
	}

	public function actions(&$actions): void
	{
		$statusController = $this->container->get(StatusController::class);
		$commentController = $this->container->get(CommentController::class);

		$actions['breezeStatus'] = [false, [$statusController, 'dispatch']];
		$actions['breezeComment'] = [false, [$commentController, 'dispatch']];
		$actions['breezeWall'] = [false, WallController::class . '::dispatch#'];
		$actions['breezeBuddy'] = [false, BuddyController::class . '::dispatch#'];
		$actions['breezeMood'] = [false, MoodController::class . '::dispatch#'];
	}

	public function profilePopUpWrapper(&$profile_items): void
	{
		$this->container->get(UserService::class)->hookProfilePopUp($profile_items);
	}

	public function alertsPrefWrapper(array &$alertTypes, &$groupOptions): void
	{
		$this->container->get(UserService::class)->hookAlertsPref($alertTypes);
	}

	public function updateLikesWrapper($type, $content, $sa, $js, $extra)
	{
		if (!$this->isEnable(SettingsEntity::MASTER)) {
			return false;
		}

		/** @var PermissionsService */
		$permissions = $this->container->get(PermissionsService::class);

		return [
			'can_see' => $permissions->get('likes_view'),
			'can_like' => $permissions->get('likes_like'),
			'type' => $type,
			'flush_cache' => true,
			'callback' => '',
		];
	}

	public function displayMoodWrapper(array &$data, int $userId, $displayCustomFields): void
	{
		/** @var MoodService */
		$moodService = $this->container->get(MoodService::class);

		$moodService->displayMood($data, $userId);
	}

	public function displayMoodProfileWrapper(int $userId, string $profileArea): void
	{
		if (!$this->isEnable(SettingsEntity::MASTER) ||
			!$this->isEnable(SettingsEntity::ENABLE_MOOD) ||
			!in_array($profileArea, MoodService::DISPLAY_PROFILE_AREAS)) {
			return;
		}

		/** @var MoodService */
		$moodService = $this->container->get(MoodService::class);

		$moodService->showMoodOnCustomFields($userId);
	}

	public function adminMenuWrapper(array &$adminMenu): void
	{
		/** @var AdminController */
		$adminController = $this->container->get(AdminController::class);

		$this->setLanguage('BreezeAdmin');

		$adminMenu['config']['areas'][AdminService::AREA] = [
			'label' => $this->getText(AdminService::AREA . '_main_title'),
			'function' => [$adminController, 'dispatch'],
			'icon' => 'smiley',
			'subsections' => [
				'main' => [$this->getText(AdminService::AREA . '_main_title')],
				'settings' => [$this->getText(AdminService::AREA . '_settings_title')],
				'permissions' => [$this->getText(AdminService::AREA . '_permissions_title')],
			],
		];

		if ($this->isEnable(SettingsEntity::ENABLE_MOOD)) {
			$adminMenu['config']['areas'][AdminService::AREA]['subsections']['moodList'] = [
				$this->getText(AdminService::AREA . '_moodList_title')
			];
		}

		// Pay no attention to that woman behind the curtain!
		$adminMenu['config']['areas'][AdminService::AREA]['subsections']['donate'] = [
			$this->getText(AdminService::AREA . '_donate_title')
		];
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
