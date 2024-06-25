<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Entity\SettingsEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\PermissionsEnum;
use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Traits\PermissionsTrait;
use Breeze\Traits\SettingsTrait;
use Breeze\Traits\TextTrait;
use Breeze\Util\Components;

class ProfileService implements ProfileServiceInterface
{
	use SettingsTrait;
	use TextTrait;
	use PermissionsTrait;

	public const AREA = 'summary';
	public const LEGACY_AREA = 'legacy';
	public const LEGACY_URL = '?action=profile;area=' . self::LEGACY_AREA . ';u=%d';
	public const URL = '%s?action=profile;area=' . self::AREA . ';u=%d';

	public const MIN_INFO_KEYS = [
		'link',
		'name',
		'avatar',
	];

	public function __construct(
		protected UserRepositoryInterface $userRepository,
		protected Components $components,
		protected PermissionsService $permissionsService
	) {
	}

	public function loadComponents(int $profileId = 0): void
	{
		$context = $this->global('context');
		$wallUserSettings = $this->userRepository->getById($profileId);
		$editorContext = &$context['controls']['richedit'][Breeze::NAME];

		$this->components->loadUIVars([
			'profileId' => $profileId,
			'pagination' => $wallUserSettings[UserSettingsEntity::PAGINATION_NUM],
			'editorId' => Breeze::NAME,
			'editorOptions' => $editorContext['sce_options'],
		]);
		$this->components->loadTxtVarsFor(['general', 'error', 'like', 'tabs']);
		$this->components->loadJavaScriptFile(Components::FOLDER . 'main.' . Breeze::REACT_HASH . '.js', [
			'external' => false,
			'defer' => true,
		], strtolower(Breeze::PATTERN . Breeze::REACT_HASH));

		$this->components->loadCSSFile(Components::CSS_FILE, [], 'smf_breeze');
	}

	public function setEditor(): void
	{
		$this->requireOnce('Subs-Editor');

		create_control_richedit([
			'id' => Breeze::NAME,
			'value' => '',
			'labels' => [
				'post_button' => $this->getText('general_save'),
			],
			'height' => '150px',
			'width' => '100%',
			'preview_type' => 0,
			'required' => true,
		]);
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

		$scriptUrl = $this->global(Breeze::SCRIPT_URL);
		$currentUserInfo = $this->global('user_info');
		$currentUserSettings = $this->getCurrentUserSettings();

		if (!empty($currentUserSettings[UserSettingsEntity::WALL]) ||
			$this->isEnable(SettingsEntity::FORCE_WALL)) {
			foreach ($profile_items as &$profileItem) {
				if ($profileItem['area'] === 'summary') {
					$profileItem['area'] = self::LEGACY_AREA;

					break;
				}
			}
			unset($profileItem);
		}

		$profile_items[] = [
			'menu' => 'breeze_profile',
			'area' => 'alerts',
			'url' => sprintf(self::URL, $scriptUrl, $currentUserInfo['id']),
			'title' => $this->getText('general_my_wall_settings'),
		];
	}

	public function hookAlertsPref(array &$alertTypes): void
	{
		if (!$this->isEnable(SettingsEntity::MASTER)) {
			return;
		}

		$this->setLanguage('alerts');

		$alertTypes['breezeComponents'] = [
			Breeze::PATTERN . 'status_owner' => [
				'alert' => 'yes',
				'email' => 'never',
			],
			Breeze::PATTERN . 'comment_status_owner' => [
				'alert' => 'yes',
				'email' => 'never',
			],
			Breeze::PATTERN . 'comment_profile_owner' => [
				'alert' => 'yes',
				'email' => 'never',
			],
			Breeze::PATTERN . 'mention' => [
				'alert' => 'yes',
				'email' => 'never',
			],
			Breeze::PATTERN . 'like' => [
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

		if (!$this->isAllowedTo(PermissionsEnum::PROFILE_VIEW)) {
			return false;
		}

		if (!empty($profileSettings['kick_ignored']) && !empty($profileSettings['ignoredList'])) {
			$profileIgnoredList = array_map('intval', explode(',', $profileSettings['ignoredList']));

			if (in_array($userId, $profileIgnoredList, true)) {
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
			$ignored = array_map('intval', explode(',', $userStalkedSettings['ignoredList']));

			return in_array($user_info['id'], $ignored, true);
		}

		return false;
	}
}
