<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;


class UserService extends BaseService implements ServiceInterface
{
	public const AREA = 'breezeSettings';
	public const LEGACY_AREA = 'legacy';

	public function getCurrentUserSettings(): array
	{
		$currentUserInfo = $this->global('user_info');

		return $this->repository->getUserSettings($currentUserInfo['id']);
	}

	public function getUserSettings(int $userId): array
	{
		return $this->repository->getUserSettings($userId);
	}

	public function hookProfilePopUp(&$profile_items): void
	{
		if (!$this->enable('master'))
			return;

		$this->setLanguage(Breeze::NAME);

		$scriptUrl = $this->global('scripturl');
		$currentUserInfo = $this->global('user_info');
		$currentUserSettings = $this->getCurrentUserSettings();

		if ($this->enable('force_enable') || !empty($currentUserSettings['wall']))
			foreach ($profile_items as &$profileItem)
				if ('summary' === $profileItem['area'])
				{
					$profileItem['area'] = self::LEGACY_AREA;
					break;
				}

		$profile_items[] = [
			'menu' => 'breeze_profile',
			'area' => 'alerts',
			'url' => $scriptUrl . '?action=profile;area=' . self::AREA . ';u=' . $currentUserInfo['id'],
			'title' => $this->getText('general_my_wall_settings'),
		];
	}

	public function hookAlertsPref(array &$alertTypes): void
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

	public function loadUsersInfo(array $ids = []): array
	{
		$userIds = $ids;
		$loadedUsers = [];

		if (empty($userIds))
			return $loadedUsers;

		$modSettings = $this->global('modSettings');
		$loadedIDs = loadMemberData($userIds);

		foreach ($userIds as $userId)
		{
			if (!in_array($userId, $loadedIDs))
			{
				$loadedUsers[$userId] = [
					'link' => $this->getSmfText('guest_title'),
					'name' => $this->getSmfText('guest_title'),
					'avatar' => ['href' => $modSettings['avatar_url'] . '/default.png']
				];
				continue;
			}

			$loadedUsers[$userId] = loadMemberContext($userId, true);
		}

		return $loadedUsers;
	}
}
