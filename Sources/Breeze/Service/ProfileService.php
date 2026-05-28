<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Entity\SettingsEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Enums\PermissionsEnum;
use Breeze\Repository\User\SettingsRepositoryInterface as UserSettingsRepository;
use Breeze\Traits\PermissionsTrait;
use Breeze\Traits\SettingsTrait;
use Breeze\Traits\TextTrait;
use Breeze\Util\Components;
use Breeze\Util\Response;

class ProfileService extends BaseService implements ProfileServiceInterface
{
	use SettingsTrait;
	use TextTrait;
	use PermissionsTrait;

	public const string AREA = 'summary';
	public const string SETTINGS_AREA = 'breezeSettings';
	public const string LEGACY_AREA = 'legacy';
	public const string LEGACY_URL = '?action=profile;area=' . self::LEGACY_AREA . ';u=%d';
	public const string URL = '%s?action=profile;area=' . self::AREA . ';u=%d';
	public const string SETTINGS_URL = '%s?action=profile;area=' . self::SETTINGS_AREA . ';u=%d';

	public const array MIN_INFO_KEYS = [
		'link',
		'name',
		'avatar',
	];

	public function __construct(
		protected UserSettingsRepository $userSettingsRepository,
		protected Components $components,
		protected PermissionsServiceInterface $permissionsService,
	) {
		parent::__construct($userSettingsRepository);
	}

	public function loadComponents(int $profileId = 0): void
	{
		$context = $this->global('context');
		$userInfo = $this->getCurrentUserInfo();
		$currentUserId = (int) ($userInfo['id'] ?? 0);
		$wallUserSettings = $this->userSettingsRepository->getById($profileId);
		$currentUserSettings = $currentUserId === $profileId
			? $wallUserSettings
			: $this->userSettingsRepository->getById($currentUserId);
		$editorContext = $context['controls']['richedit'][Breeze::NAME];
		$token = createToken(Response::CSRF_TOKEN_ACTION, 'get');

		$this->components->loadUIVars([
			'profileId' => $profileId,
			'pagination' => $wallUserSettings->getPaginationNumber(),
			'editorId' => Breeze::NAME,
			'editorOptions' => $editorContext['sce_options'],
			'editorIsRich' => $editorContext['rich_active'],
			'currentUserAvatar' => $userInfo['avatar']['url'],
			'isCurrentUserOwner' => $currentUserId === $profileId,
			'canShowAddBuddyButton' => $this->canShowAddBuddyButton($profileId, $currentUserId, $wallUserSettings),
			UserSettingsEntity::ENABLE_BUDDIES_TAB => $wallUserSettings->getEnableBuddiesTab(),
			UserSettingsEntity::ABOUT_ME => !in_array($wallUserSettings->getAboutMe(), ['', '0'], true),
			UserSettingsEntity::CONFIRM_POST => $currentUserSettings->getConfirmPost(),
			'csrfTokenVar' => $token[Response::CSRF_TOKEN_ACTION . '_token_var'],
			'csrfTokenValue' => $token[Response::CSRF_TOKEN_ACTION . '_token'],
		]);
		$this->components->loadTxtVarsFor(['general', 'error', 'like', 'tabs']);
		$this->components->loadComponents();
	}

	public function canShowAddBuddyButton(
		int $profileId = 0,
		int $userId = 0,
		?UserSettingsEntity $wallUserSettings = null
	): bool
	{
		// Check 1: Not viewing your own wall
		if ($userId === $profileId) {
			return false;
		}

		// Check 2: Already a buddy
		$userInfo = $this->getCurrentUserInfo();
		$buddies = array_map('intval', $userInfo['buddies'] ?? []);
		if (in_array($profileId, $buddies, true)) {
			return false;
		}

		// Check 3: Blocked — block always beats buddy (unconditional)
		if ($wallUserSettings === null) {
			$wallUserSettings = $this->userSettingsRepository->getById($profileId);
		}

		$blockList = $wallUserSettings->getBlockList();

		if (!empty($blockList) && in_array($userId, $blockList, true)) {
			return false;
		}

		return true;
	}

	public function updateMemberData(int $userId, array $updatedData): void
	{
		updateMemberData($userId, $updatedData);
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

	public function getCurrentUserSettings(): UserSettingsEntity
	{
		$currentUserInfo = $this->global('user_info');

		return $this->userSettingsRepository->getById($currentUserInfo['id']);
	}

	public function getUserSettings(int $userId): UserSettingsEntity
	{
		return $this->userSettingsRepository->getById($userId);
	}

	public function loadUsersInfo(array $userIds = []): array
	{
		$usersInfo = parent::loadUsersInfo($userIds);
		$userIds = array_keys($usersInfo);
		$settingsById = $this->userSettingsRepository->getByIds($userIds);

		foreach ($usersInfo as $userId => &$userData) {
			$settings = $settingsById[(int) $userId] ?? null;
			$userData['blockList'] = $settings?->getBlockList() ?? [];
		}

		return $usersInfo;
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

		if ($currentUserSettings->getWall() !== 0) {
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
			'area' => self::SETTINGS_AREA,
			'url' => sprintf(self::SETTINGS_URL, $scriptUrl, $currentUserInfo['id']),
			'title' => $this->getText('general_my_wall_settings'),
		];
	}

	public function hookAlertsPref(array &$alertTypes): void
	{
		if (!$this->isEnable(SettingsEntity::MASTER)) {
			return;
		}

		$this->setLanguage('BreezeAlerts');

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

	public function isAllowedToSeePage(UserSettingsEntity $profileSettings, int $profileId = 0, int $userId = 0): bool
	{
		$isWallEnable = $profileSettings->getWall() !== 0;
		$blockList = $profileSettings->getBlockList();

		if (!$isWallEnable) {
			return false;
		}

		if (!$this->isAllowedTo(PermissionsEnum::PROFILE_VIEW)) {
			return false;
		}

		// One-way: wall owner has blocked the viewer.
		if (in_array($userId, $blockList, true)) {
			return false;
		}

		// Symmetric: viewer has blocked the wall owner.
		// Skip for guests (userId = 0) and when profileId is unknown.
		if ($userId !== 0 && $profileId !== 0) {
			$viewerBlockList = $this->userSettingsRepository->getById($userId)->getBlockList();

			if (in_array($profileId, $viewerBlockList, true)) {
				return false;
			}
		}

		return true;
	}
}
