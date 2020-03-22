<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Controller\User\Settings\Alerts as AlertSettingsController;
use Breeze\Controller\User\Settings\Cover as CoverSettingsController;
use Breeze\Controller\User\Settings\General as GeneralSettingsController;
use Breeze\Controller\User\Wall as WallController;
use Breeze\Model\UserModel as UserModel;

class UserService extends BaseService implements ServiceInterface
{
	public function getCurrentUserSettings(): array
	{
		$currentUserInfo = $this->global('user_info');

		return $this->repository->getUserSettings($currentUserInfo['id']);
	}

	public function hookProfilePopUp(&$profile_items): void
	{
		if (!$this->enable('master'))
			return;

		$scriptUrl = $this->global('scripturl');
		$currentUserInfo = $this->global('user_info');
		$currentUserSettings = $this->getCurrentUserSettings();

		if ($this->enable('force_enable') || !empty($currentUserSettings['wall']))
			foreach ($profile_items as &$item)
				if ('summary' == $item['area'])
					$item['area'] = 'static';

		$profile_items[] = [
			'menu' => 'breeze_profile',
			'area' => 'alerts',
			'url' => $scriptUrl . '?action=profile;area=breezesettings;u=' . $currentUserInfo['id'],
			'title' => $this->getText('general_my_wall_settings'),
		];
	}

	public function hookProfileMenu(&$profile_areas): void
	{
		if (!$this->enable('master'))
			return;

		$context = $this->global('context');
		$currentUserSettings = $currentUserSettings = $this->getCurrentUserSettings();

		if ($this->enable('force_enable') || !empty($currentUserSettings['wall']))
		{
			$profile_areas['info']['areas']['summary'] = [
				'label' => $this->text->get('general_wall'),
				'icon' => 'smiley',
				'file' => false,
				'function' => WallController::class . '::do#',
				'permission' => [
					'own' => 'is_not_guest',
					'any' => 'profile_view',
				],
			];

			$profile_areas['info']['areas']['static'] = [
				'label' => $this->text->get('general_summary'),
				'icon' => 'members',
				'file' => 'Profile-View.php',
				'function' => 'summary',
				'permission' => [
					'own' => 'is_not_guest',
					'any' => 'profile_view',
				],
			];
		}

		$profile_areas['breeze_profile'] = [
			'title' => $this->text->get('general_my_wall_settings'),
			'areas' => [],
		];

		$profile_areas['breeze_profile']['areas']['settings'] = [
			'label' => $this->text->get('user_settings_name'),
			'icon' => 'maintain',
			'file' => false,
			'function' => GeneralSettingsController::class . '::do#',
			'enabled' => $context['user']['is_owner'],
			'permission' => [
				'own' => 'is_not_guest',
				'any' => 'profile_view',
			],
		];

		$profile_areas['breeze_profile']['areas']['alerts'] = [
			'label' => $this->text->get('user_settings_name_alerts'),
			'file' => false,
			'function' => AlertSettingsController::class . '::do#',
			'enabled' => $context['user']['is_owner'],
			'icon' => 'maintain',
			'subsections' => [
				'settings' => [
					$this->text->get('user_settings_name_alerts_settings'),
					['is_not_guest', 'profile_view']],
				'edit' => [
					$this->text->get('user_settings_name_alerts_edit'),
					['is_not_guest', 'profile_view']],
			],
			'permission' => [
				'own' => 'is_not_guest',
				'any' => 'profile_view',
			],
		];

		if ($this->enable('cover'))
			$profile_areas['breeze_profile']['areas']['cover'] = [
				'label' => $this->text->get('user_settings_name_cover'),
				'icon' => 'administration',
				'file' => false,
				'function' => CoverSettingsController::class . '::do#',
				'enabled' => $context['user']['is_owner'],
				'permission' => [
					'own' => 'is_not_guest',
					'any' => 'profile_view',
				],
			];
	}

	public function hookAlertsPref(array $alertTypes): void
	{
		if (!$this->enable('master'))
			return;

		$this->text->setLanguage('alerts');

		$alertTypes['breeze'] = [
			'' . Breeze::PATTERN . 'status_owner' => [
				'alert' => 'yes',
				'email' => 'never'
			],
			'' . Breeze::PATTERN . 'comment_status_owner' => [
				'alert' => 'yes',
				'email' => 'never'
			],
			'' . Breeze::PATTERN . 'comment_profile_owner' => [
				'alert' => 'yes',
				'email' => 'never'
			],
			'' . Breeze::PATTERN . 'mention' => [
				'alert' => 'yes',
				'email' => 'never'
			],
			'' . Breeze::PATTERN . 'like' => [
				'alert' => 'yes',
				'email' => 'never'
			],
		];
	}

	public function stalkingCheck(int $userStalkedId = 0): bool
	{
		$user_info = $this->global('user_info');

		if (empty($userId))
			return true;

		$userStalkedSettings = $this->userModel->getUserSettings($userStalkedId);

		if (!empty($userStalkedSettings['kick_ignored']) && !empty($userStalkedSettings['ignoredList']))
		{
			$ignored = explode(',', $userStalkedSettings['ignoredList']);

			return in_array($user_info['id'], $ignored);
		}

		return false;
	}

	public function floodControl(int $userId = 0): bool
	{
		if (empty($userId))
			return false;

		$seconds = 60 * ($this->get('flood_minutes', 5));
		$messages = $this->get('flood_messages', 10);

		// Has it been defined yet?
		if (!isset($_SESSION['Breeze_floodControl' . $userId]))
			$_SESSION['Breeze_floodControl' . $userId] = [
				'time' => time() + $seconds,
				'messagesCount' => 0,
			];

		$_SESSION['Breeze_floodControl' . $userId]['messagesCount']++;

		// Short name.
		$flood = $_SESSION['Breeze_floodControl' . $userId];

		// Chatty one huh?
		if ($flood['messagesCount'] >= $messages && time() <= $flood['time'])
			return false;

		// Enough time has passed, give the user some rest.
		if (time() >= $flood['time'])
			unset($_SESSION['Breeze_floodControl' . $userId]);

		return true;
	}
}
