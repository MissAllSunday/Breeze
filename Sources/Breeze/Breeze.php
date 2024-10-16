<?php

declare(strict_types=1);

namespace Breeze;

use Breeze\Config\MapperAggregate;
use Breeze\Controller\AdminController;
use Breeze\Controller\API\CommentController;
use Breeze\Controller\API\LikesController;
use Breeze\Controller\API\StatusController;
use Breeze\Controller\BuddyController;
use Breeze\Controller\User\Settings\UserSettingsController;
use Breeze\Controller\User\WallController;
use Breeze\Entity\SettingsEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\User\UserRepository;
use Breeze\Service\Actions\AdminServiceInterface;
use Breeze\Service\PermissionsService;
use Breeze\Service\ProfileService;
use Breeze\Traits\RequestTrait;
use Breeze\Traits\TextTrait;
use Breeze\Util\Validate\DataNotFoundException;
use League\Container\Container as Container;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Breeze
{
	use TextTrait;
	use RequestTrait;

	public const NAME = 'Breeze';
	public const VERSION = '2.0';
	public const PATTERN = self::NAME . '_';
	public const FEED = 'https://api.github.com/repos/MissAllSunday/Breeze/releases';
	public const SUPPORT_URL = 'https://missallsunday.com';
	public const REACT_DOM_VERSION = '18.2.0';
	public const REACT_VERSION = '18.2.0';
	public const REACT_HASH = 'bb5a0ebe';
	public const ACTIONS = [
		'breezeStatus',
		'breezeComment',
		'wall',
		'breezeBuddy',
		'breezeLike',
	];
	public const SCRIPT_URL ='scripturl';

	protected Container $container;

	public function __construct()
	{
		$this->container = new Container();
		$mappers = (new MapperAggregate())->getMappers();

		foreach ($mappers as $mapperFile) {
			foreach ($mapperFile as $mapperInfo) {
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

	/**
	 * @throws DataNotFoundException
	 */
	public function profileMenuWrapper(array &$profileAreas): void
	{
		if (!$this->isEnable(SettingsEntity::MASTER)) {
			return;
		}

		$this->setLanguage(self::NAME);
		$context = $this->global('context');
		$userInfo = $this->global('user_info');
		$currentUserSettings = $this->container->get(UserRepository::class)->getById($userInfo['id']);

		if (!empty($currentUserSettings['wall']) || $this->isEnable(SettingsEntity::FORCE_WALL)) {
			/** @var WallController $wallController */
			$wallController = $this->container->get(WallController::class);

			$profileAreas['info']['areas']['summary'] = [
				'label' => $this->getText('general_wall'),
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
					'label' => $this->getText(ProfileService::AREA . '_main_title'),
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
		$currentUserSettings = $this->container->get(UserRepository::class)->getById($currentUserInfo['id']);

		if (!empty($menu_buttons['profile']['sub_buttons']['summary'])) {
			$menu_buttons['profile']['sub_buttons']['summary'] = [
				'title' => $this->getText('summary'),
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
			['wall' => [
				'title' => $this->getText(UserSettingsEntity::GENERAL_WALL),
				'icon' => 'smiley',
				'href' => $scriptUrl . '?action=wall',
				'show' =>
					!$currentUserInfo['is_guest'] &&
					!empty($currentUserSettings[UserSettingsEntity::GENERAL_WALL]),
				'sub_buttons' => [
					'noti' => [
						'title' => $this->getText('user_notisettings_name'),
						'href' => $scriptUrl . '?action=profile;area=alerts;sa=edit;u=' . $currentUserInfo['id'],
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
	}

	public function actions(array &$actions): void
	{
		$action = $this->getRequest('action', '');

		if (empty($action) || !in_array($action, self::ACTIONS, true)) {
			return;
		}

		try {
			$statusController = $this->container->get(StatusController::class);
			$commentController = $this->container->get(CommentController::class);
			$likesController = $this->container->get(LikesController::class);
			$wallController = $this->container->get(WallController::class);

			$actions['breezeStatus'] = [false, fn () => $statusController->dispatch()];
			$actions['breezeComment'] = [false, fn () => $commentController->dispatch()];
			$actions['wall'] = [false, [$wallController, 'dispatch']];
			$actions['breezeBuddy'] = [false, BuddyController::class . '::dispatch#'];
			$actions['breezeLike'] = [false, fn () => $likesController->dispatch()];
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

	public function alertsPrefWrapper(array &$alertTypes, &$groupOptions): void
	{
		$this->container->get(ProfileService::class)->hookAlertsPref($alertTypes);
	}

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
