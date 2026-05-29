<?php

declare(strict_types=1);

namespace Breeze;

use Breeze\Config\DependenciesServiceProvider;
use Breeze\Controller\AdminController;
use Breeze\Controller\API\CommentController;
use Breeze\Controller\API\LikesController;
use Breeze\Controller\API\StatusController;
use Breeze\Controller\User\Settings\UserSettingsController;
use Breeze\Controller\User\WallController;
use Breeze\Entity\SettingsEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Enums\PermissionsEnum;
use Breeze\Repository\User\SettingsRepository as UserSettingsRepository;
use Breeze\Service\Actions\AdminServiceInterface;
use Breeze\Service\AlertService;
use Breeze\Service\PermissionsService;
use Breeze\Service\ProfileService;
use Breeze\Traits\PermissionsTrait;
use Breeze\Traits\RequestTrait;
use Breeze\Traits\TextTrait;
use Breeze\Util\Validate\DataNotFoundException;
use League\Container\Container as Container;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @codeCoverageIgnore
 */
class Breeze
{
	use TextTrait;
	use RequestTrait;
	use PermissionsTrait;

	public const string NAME = 'Breeze';
	public const string VERSION = '2.0';
	public const string PATTERN = self::NAME . '_';
	public const string FEED = 'https://api.github.com/repos/MissAllSunday/Breeze/releases';
	public const string SUPPORT_URL = 'https://missallsunday.com';
	public const string REACT_DOM_VERSION = '19.1.2';
	public const string REACT_VERSION = '19.1.2';
	public const string REACT_HASH = 'index-wJ0skmG2';

	public const string ACTION_STATUS = 'breezeStatus';
	public const string ACTION_COMMENT = 'breezeComment';
	public const string ACTION_LIKE = 'breezeLike';
	public const string ACTION_WALL = 'wall';
	public const string ACTION_PROFILE = 'profile';
	public const array ACTIONS = [
		self::ACTION_STATUS => StatusController::class,
		self::ACTION_COMMENT => CommentController::class,
		self::ACTION_LIKE => LikesController::class,
		self::ACTION_WALL => WallController::class,
	];
	public const string SCRIPT_URL ='scripturl';

	protected Container $container;

	public function __construct()
	{
		try {
			$this->container = new Container();
			$this->container->addServiceProvider(new DependenciesServiceProvider());
		} catch (NotFoundExceptionInterface|ContainerExceptionInterface $exception) {
			log_error($exception->getMessage());
		}
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function permissionsWrapper(array &$permissionGroups, array &$permissionList): void
	{
		$this->container->get(PermissionsService::class)->hookPermissions($permissionGroups, $permissionList);
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function profileMenuWrapper(array &$profileAreas): void
	{
		if (!$this->isEnable(SettingsEntity::MASTER)) {
			return;
		}

		$this->setLanguage(self::NAME);
		$context = $this->global('context');
		$userInfo = $this->global('user_info');
		$currentUserSettings = $this->container->get(UserSettingsRepository::class)->getById($userInfo['id']);

		if (!empty($currentUserSettings->getWall())) {
			/** @var WallController $wallController */
			$wallController = $this->container->get(WallController::class);

			$profileAreas['info']['areas']['summary'] = [
				'label' => $this->getText('tabs_wall'),
				'icon' => 'smiley',
				'function' => fn () => $wallController->dispatch(),
				'permission' => [
					'own' => 'is_not_guest',
					'any' => 'profile_view',
				],
			];

			$profileAreas['info']['areas'][ProfileService::LEGACY_AREA] = [
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

		$profileAreas['breeze_profile'] = [
			'title' => $this->getText('general_my_wall_settings'),
			'areas' => [
				ProfileService::SETTINGS_AREA => [
					'label' => $this->getText(ProfileService::SETTINGS_AREA . '_main_title'),
					'icon' => 'maintain',
					'function' => fn () => $settingsController->dispatch(),
					'enabled' => $context['user']['is_owner'],
					'permission' => [
						'own' => 'is_not_guest',
						'any' => 'profile_view',
					],
				],
			],
		];
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function menu(array &$menu_buttons): void
	{
		if (!$this->isEnable(SettingsEntity::MASTER)) {
			return;
		}

		$scriptUrl = $this->global(self::SCRIPT_URL);
		$currentUserInfo = $this->global('user_info');

		try {
			$currentUserSettings = $this->container->get(UserSettingsRepository::class)->getById($currentUserInfo['id']);
			if (!empty($menu_buttons['profile']['sub_buttons']['summary'])) {
				$menu_buttons['profile']['sub_buttons']['summary'] = [
					'title' => $this->getText('general_summary'),
					'href' => $scriptUrl . '?action=profile;area=' . ProfileService::LEGACY_AREA,
					'show' => true,
				];
			}

			$menuReference = 'home';
			$counter = 0;

			foreach (array_keys($menu_buttons) as $area) {
				$counter++;
				if ($area === $menuReference) {
					break;
				}
			}
			$menu_buttons = array_merge(
				array_slice($menu_buttons, 0, $counter),
				[self::ACTION_WALL => [
					'title' => $this->getText(UserSettingsEntity::GENERAL_WALL),
					'icon' => 'smiley',
					'href' => $scriptUrl . '?action=' . self::ACTION_WALL,
					'show' =>
						!$currentUserInfo['is_guest'] &&
						!empty($currentUserSettings->getGeneralWall()) &&
						$this->isAllowedTo(PermissionsEnum::VIEW_GENERAL_WALL),
					'sub_buttons' => [
						'noti' => [
							'title' => $this->getText('general_my_wall_settings'),
							'href' => $scriptUrl . '?action=profile;area=breezeSettings;u=' . $currentUserInfo['id'],
							'show' => !$currentUserInfo['is_guest'],
							'sub_buttons' => [],
						],
						'admin' => [
							'title' => $this->getText('admin'),
							'href' => $scriptUrl . '?action=admin;area=' . AdminServiceInterface::AREA,
							'show' => $currentUserInfo['is_admin'],
							'sub_buttons' => [],
						],
					],
				]],
				array_slice($menu_buttons, $counter)
			);
		} catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
			log_error($e->getMessage());
		}
	}

	public function actions(array &$actions): void
	{
		$action = $this->getRequest('action', '');

		if (empty($action) || !array_key_exists($action, self::ACTIONS)) {
			return;
		}

		try {
			$controller = $this->container->get(self::ACTIONS[$action]);
			$actions[$action] = [false, fn () => $controller->dispatch()];
		} catch (NotFoundExceptionInterface|ContainerExceptionInterface $exception) {
			log_error($exception->getMessage());
		}
	}

	public function profilePopUpWrapper(&$profile_items): void
	{
		try {
			$this->container->get(ProfileService::class)->hookProfilePopUp($profile_items);
		} catch (NotFoundExceptionInterface|ContainerExceptionInterface $exception) {
			log_error($exception->getMessage());
		}
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function alertsPrefWrapper(array &$alertTypes): void
	{
		$this->container->get(ProfileService::class)->hookAlertsPref($alertTypes);
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function alertsWrapper(array &$alerts): void
	{
		$alertService = $this->container->get(AlertService::class);
		$alertService->handle($alerts);
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function adminMenuWrapper(array &$adminMenu): void
	{
		/** @var AdminController $adminController */
		$adminController = $this->container->get(AdminController::class);

		$this->setLanguage('BreezeAdmin');

		$adminMenu['config']['areas'][AdminServiceInterface::AREA] = [
			'label' => $this->getText(AdminServiceInterface::AREA . '_main_title'),
			'function' => fn () => $adminController->dispatch(),
			'icon' => 'smiley',
			'subsections' => [
				'main' => [$this->getText(AdminServiceInterface::AREA . '_main_title')],
				'settings' => [$this->getText(AdminServiceInterface::AREA . '_settings_title')],
				'permissions' => [$this->getText(AdminServiceInterface::AREA . '_permissions_title')],
				'maintenance' => [$this->getText(AdminServiceInterface::AREA . '_maintenance_title')],
				'donate' => [$this->getText(AdminServiceInterface::AREA . '_donate_title'),],
			],
		];
	}

	public static function credits(): array
	{
		return [
			'dev' => [
				'name' => 'Developer(s)',
				'users' => [
					'suki' => [
						'name' => 'Breeze © ' . date('Y') . ' Michel Mendiola',
						'site' => 'https://missallsunday.com',
					],
				],
			],
			'scripts' => [
				'name' => 'Third Party Scripts',
				'users' => [
					'React' => [
						'name' => 'React',
						'site' => 'https://reactjs.org',
					],
					'ReactDOM' => [
						'name' => 'ReactDOM',
						'site' => 'https://reactjs.org',
					],
					'ReactHotToast' => [
						'name' => 'React Hot Toast',
						'site' => 'https://react-hot-toast.com',
					],
					'LeagueContainer' => [
						'name' => 'League Container',
						'site' => 'https://container.thephpleague.com',
					],
					'LeagueEvent' => [
						'name' => 'League Event',
						'site' => 'https://event.thephpleague.com',
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
