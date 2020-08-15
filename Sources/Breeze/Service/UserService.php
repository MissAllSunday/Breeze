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

	private UserRepositoryInterface $userRepository;

	public function __construct(UserRepositoryInterface $userRepository)
	{
		$this->userRepository = $userRepository;
	}

	public function getCurrentUserInfo(): array
	{
		return $this->global('user_info');
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

	public function getUsersToLoad($userIds = []): array
	{
		return loadMemberData($userIds);
	}

	public function loadUsersInfo(array $userIds = []): array
	{
		$loadedUsers = [];

		$modSettings = $this->global('modSettings');
		$loadedIDs = $this->getUsersToLoad($userIds);

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
