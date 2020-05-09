<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Entity\SettingsEntity;
use Breeze\Repository\User\UserRepositoryInterface;


class UserService extends BaseService implements UserServiceInterface
{
	public const MIN_INFO_KEYS = [
		'link',
		'name',
		'avatar'
	];

	/**
	 * @var UserRepositoryInterface
	 */
	private $userRepository;

	public function __construct(UserRepositoryInterface $userRepository)
	{
		$this->userRepository = $userRepository;
	}

	public function getCurrentUserSettings(): array
	{
		$currentUserInfo = $this->global('user_info');

		return $this->userRepository->getUserSettings($currentUserInfo['id']);
	}

	public function getUserSettings(int $userId): array
	{
		return $this->userRepository->getUserSettings($userId);
	}

	public function hookProfilePopUp(&$profile_items): void
	{
		if (!$this->isEnable(SettingsEntity::MASTER))
			return;

		$this->setLanguage(Breeze::NAME);

		$scriptUrl = $this->global('scripturl');
		$currentUserInfo = $this->global('user_info');
		$currentUserSettings = $this->getCurrentUserSettings();

		if ($this->isEnable(SettingsEntity::FORCE_WALL) || !empty($currentUserSettings['wall']))
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
		if (!$this->isEnable(SettingsEntity::MASTER))
			return;

		$this->setLanguage('alerts');

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

		$userStalkedSettings = $this->userRepository->getUserSettings($userStalkedId);

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

		$seconds = 60 * ($this->getSetting('flood_minutes', 5));
		$messages = $this->getSetting('flood_messages', 10);

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

	public function loadUsersInfo(array $userIds = [], $noGuest = false): array
	{
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

	public function areInvalidUsers(array $userIds): array
	{
		$usersInfo = $this->loadUsersInfo($userIds, true);

		return empty(array_diff_key($userIds, $usersInfo));
	}
}
