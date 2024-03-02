<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Entity\SettingsEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Traits\SettingsTrait;
use Breeze\Traits\TextTrait;
use Breeze\Util\Components;
use Breeze\Util\Permissions;

class ProfileService implements ProfileServiceInterface
{
	use SettingsTrait;
	use TextTrait;

	public const AREA = 'summary';
	public const LEGACY_AREA = 'legacy';
	public const LEGACY_URL = '?action=profile;area=' . self::LEGACY_AREA . ';u=%d';

	public const MIN_INFO_KEYS = [
		'link',
		'name',
		'avatar',
	];

	public function __construct(
		protected UserRepositoryInterface $userRepository,
		protected Components $components
	) {
	}

	public function loadComponents(int $profileId = 0): void
	{
		$wallUserSettings = $this->userRepository->getById($profileId);

		$this->components->loadUIVars([
			'profileId' => $profileId,
			'pagination' => $wallUserSettings[UserSettingsEntity::PAGINATION_NUM],
		]);
		$this->components->loadTxtVarsFor(['general', 'error', 'like']);
		$this->components->loadJavaScriptFile('breezeComponents/main.' . Breeze::REACT_HASH . '.js', [
			'external' => false,
			'defer' => true,
		], strtolower(Breeze::PATTERN . Breeze::REACT_HASH));

		$this->components->loadCSSFile('breeze.css', [], 'smf_breeze');
	}

	public function getCurrentUserInfo(): array
	{
		return $this->global('user_info');
	}

	public function getCurrentUserSettings(): array
	{
		$currentUserInfo = $this->global('user_info');

		return $this->userRepository->getById($currentUserInfo['id']);
	}

	public function getUserSettings(int $userId): array
	{
		return $this->userRepository->getById($userId);
	}

	public function hookProfilePopUp(&$profile_items): void
	{
		if (!$this->isEnable(SettingsEntity::MASTER)) {
			return;
		}

		$this->setLanguage(Breeze::NAME);

		$scriptUrl = $this->global('scripturl');
		$currentUserInfo = $this->global('user_info');
		$currentUserSettings = $this->getCurrentUserSettings();

		if ($this->isEnable(SettingsEntity::FORCE_WALL) || !empty($currentUserSettings['wall'])) {
			foreach ($profile_items as &$profileItem) {
				if ($profileItem['area'] === 'summary') {
					$profileItem['area'] = self::LEGACY_AREA;

					break;
				}
			}
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
		if (!$this->isEnable(SettingsEntity::MASTER)) {
			return;
		}

		$this->setLanguage('alerts');

		$alertTypes['breeze'] = [
			'' . Breeze::PATTERN . 'status_owner' => [
				'alert' => 'yes',
				'email' => 'never',
			],
			'' . Breeze::PATTERN . 'comment_status_owner' => [
				'alert' => 'yes',
				'email' => 'never',
			],
			'' . Breeze::PATTERN . 'comment_profile_owner' => [
				'alert' => 'yes',
				'email' => 'never',
			],
			'' . Breeze::PATTERN . 'mention' => [
				'alert' => 'yes',
				'email' => 'never',
			],
			'' . Breeze::PATTERN . 'like' => [
				'alert' => 'yes',
				'email' => 'never',
			],
		];
	}

	public function isAllowedToSeePage(array $profileSettings, int $profileId = 0, int $userId = 0): bool
	{
		$forceWall = $this->getSetting(SettingsEntity::FORCE_WALL);
		$isCurrentUserOwner = $userId === $profileId;

		if (empty($profileSettings[UserSettingsEntity::WALL]) && !empty($forceWall)) {
			return true;
		}

		if (empty($profileSettings[UserSettingsEntity::WALL])) {
			return false;
		}

		if (!Permissions::isAllowedTo('profile_view')) {
			return false;
		}

		if (!empty($profileSettings['kick_ignored']) && !empty($profileSettings['ignoredList'])) {
			$profileIgnoredList = explode(',', $profileSettings['ignoredList']);

			if (in_array($userId, $profileIgnoredList)) {
				return false;
			}
		}

		return true;
	}

	public function stalkingCheck(int $userStalkedId = 0): bool
	{
		$user_info = $this->global('user_info');

		if (empty($user_info['id'])) {
			return true;
		}

		$userStalkedSettings = $this->userRepository->getById($userStalkedId);

		if (!empty($userStalkedSettings['kick_ignored']) && !empty($userStalkedSettings['ignoredList'])) {
			$ignored = explode(',', $userStalkedSettings['ignoredList']);

			return in_array($user_info['id'], $ignored);
		}

		return false;
	}

	public function setEditor(): void
	{
		$this->requireOnce('Subs-Editor');

		create_control_richedit([
			'id' => Breeze::NAME,
			'value' => '',
			'labels' => [
				'post_button' => $this->getText('general_send'),
			],
			'preview_type' => 2,
			'required' => true,
		]);
	}
}
