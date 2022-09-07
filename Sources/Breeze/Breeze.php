<?php

declare(strict_types=1);

namespace Breeze;

use Breeze\Config\MapperAggregate;
use Breeze\Controller\AdminController;
use Breeze\Controller\API\CommentController;
use Breeze\Controller\API\LikesController;
use Breeze\Controller\API\MoodController;
use Breeze\Controller\API\StatusController;
use Breeze\Controller\BuddyController;
use Breeze\Controller\User\Settings\AlertsController;
use Breeze\Controller\User\Settings\UserSettingsController;
use Breeze\Controller\User\WallController;
use Breeze\Entity\SettingsEntity;
use Breeze\Service\Actions\AdminServiceInterface;
use Breeze\Service\Actions\UserSettingsServiceInterface;
use Breeze\Service\MoodService;
use Breeze\Service\MoodServiceInterface;
use Breeze\Service\PermissionsService;
use Breeze\Service\UserService;
use Breeze\Service\UserServiceInterface;
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
	public const VUE_VERSION = '2.6.11';

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
			/** @var WallController $wallController */
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

			$profileAreas['info']['areas'][UserServiceInterface::LEGACY_AREA] = [
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

		/** @var UserSettingsController $settingsController */
		$settingsController = $this->container->get(UserSettingsController::class);

		/** @var AlertsController $alertsController */
		$alertsController = $this->container->get(AlertsController::class);

		$profileAreas['breeze_profile'] = [
			'title' => $this->getText('general_my_wall_settings'),
			'areas' => [],
		];

		$profileAreas['breeze_profile']['areas']['breezeSettings'] = [
			'label' => $this->getText(UserSettingsServiceInterface::AREA . '_main_title'),
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
					['is_not_guest', 'profile_view'], ],
				'edit' => [
					$this->getText('user_settings_name_alerts_edit'),
					['is_not_guest', 'profile_view'], ],
			],
			'permission' => [
				'own' => 'is_not_guest',
				'any' => 'profile_view',
			],
		];
	}

	public function menu(array &$menu_buttons): void
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
				'href' => $scriptUrl . '?action=profile;area=' . UserServiceInterface::LEGACY_AREA,
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
						'href' => $scriptUrl . '?action=admin;area=' . AdminServiceInterface::AREA,
						'show' => ($this->isEnable(SettingsEntity::MASTER) && $currentUserInfo['is_admin']),
						'sub_buttons' => [],
					],
				],
			]],
			array_slice($menu_buttons, $counter)
		);
	}

	public function actions(array &$actions): void
	{
		$statusController = $this->container->get(StatusController::class);
		$commentController = $this->container->get(CommentController::class);
		$moodController = $this->container->get(MoodController::class);
		$likesController = $this->container->get(LikesController::class);

		$actions['breezeStatus'] = [false, [$statusController, 'dispatch']];
		$actions['breezeComment'] = [false, [$commentController, 'dispatch']];
		$actions['breezeWall'] = [false, WallController::class . '::dispatch#'];
		$actions['breezeBuddy'] = [false, BuddyController::class . '::dispatch#'];
		$actions['breezeMood'] = [false, [$moodController, 'dispatch']];
		$actions['breezeLike'] = [false, [$likesController, 'dispatch']];
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

		/** @var PermissionsService $permissions */
		$permissions = $this->container->get(PermissionsService::class);

		return [
			'can_see' => $permissions->get('likes_view'),
			'can_like' => $permissions->get('likes_like'),
			'type' => $type,
			'flush_cache' => true,
			'callback' => '',
		];
	}

	public function displayMoodWrapper(array &$data, int $userId, bool $displayCustomFields): void
	{
		if (!$displayCustomFields) {
			return;
		}

		/** @var MoodServiceInterface $moodService */
		$moodService = $this->container->get(MoodService::class);

		$moodService->displayMood($data, $userId);
	}

	public function displayMoodProfileWrapper(int $userId, string $profileArea): void
	{
		if (!$this->isEnable(SettingsEntity::MASTER) ||
			!$this->isEnable(SettingsEntity::ENABLE_MOOD) ||
			!in_array($profileArea, MoodServiceInterface::DISPLAY_PROFILE_AREAS)) {
			return;
		}

		/** @var MoodServiceInterface $moodService */
		$moodService = $this->container->get(MoodService::class);

//		$moodService->showMoodOnCustomFields($userId);
	}

	public function adminMenuWrapper(array &$adminMenu): void
	{
		/** @var AdminController $adminController */
		$adminController = $this->container->get(AdminController::class);

		$this->setLanguage('BreezeAdmin');

		$adminMenu['config']['areas'][AdminServiceInterface::AREA] = [
			'label' => $this->getText(AdminServiceInterface::AREA . '_main_title'),
			'function' => [$adminController, 'dispatch'],
			'icon' => 'smiley',
			'subsections' => [
				'main' => [$this->getText(AdminServiceInterface::AREA . '_main_title')],
				'settings' => [$this->getText(AdminServiceInterface::AREA . '_settings_title')],
				'permissions' => [$this->getText(AdminServiceInterface::AREA . '_permissions_title')],
			],
		];

		if ($this->isEnable(SettingsEntity::ENABLE_MOOD)) {
			$adminMenu['config']['areas'][AdminServiceInterface::AREA]['subsections']['moodList'] = [
				$this->getText(AdminServiceInterface::AREA . '_moodList_title'),
			];
		}

		$adminMenu['config']['areas'][AdminServiceInterface::AREA]['subsections']['donate'] = [
			$this->getText(AdminServiceInterface::AREA . '_donate_title'),
		];
	}

	public static function credits(): array
	{
		return [
			'dev' => [
				'name' => 'Developer(s)',
				'users' => [
					'suki' => [
						'name' => 'Michel Mendiola',
						'site' => 'https://missallsunday.com',
					],
				],
			],
			'scripts' => [
				'name' => 'Third Party Scripts',
				'users' => [
					'Vue' => [
						'name' => 'Vue',
						'site' => 'https://vuejs.org/',
					],
					'Axios' => [
						'name' => 'Axios',
						'site' => 'https://github.com/axios/axios',
					],
					'moment' => [
						'name' => 'moment.js',
						'site' => 'https://momentjs.com/',
					],
					'vue-toast-notification' => [
						'name' => 'Vue Toast Notification',
						'site' => 'https://github.com/ankurk91/vue-toast-notification',
					],
					'DOMPurify' => [
						'name' => 'DOMPurify',
						'site' => 'https://github.com/cure53/DOMPurify',
					],
					'Sun Editor' => [
						'name' => 'Sun Editor',
						'site' => 'https://suneditor.com/',
					],
				],
			],
		];
	}
}

/*
* Whatcha gonna do, where are you gonna go
* When the darkness closes on you
* Is there anybody out there looking for you?
* Do they know what you've been through?
*/
