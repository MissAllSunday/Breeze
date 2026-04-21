<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Entity\SettingsEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\PermissionsEnum;
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
		protected PermissionsServiceInterface $permissionsService
	) {
		parent::__construct($userSettingsRepository);
	}

	public function loadComponents(int $profileId = 0): void
	{
		$context = $this->global('context');
		$userInfo = $this->getCurrentUserInfo();
		$wallUserSettings = $this->userSettingsRepository->getById($profileId);
		$editorContext = $context['controls']['richedit'][Breeze::NAME];
		$token = createToken(Response::CSRF_TOKEN_ACTION, 'get');

		$this->components->loadUIVars([
			'profileId' => $profileId,
			'pagination' => $wallUserSettings->getPaginationNumber(),
			'editorId' => Breeze::NAME,
			'editorOptions' => $editorContext['sce_options'],
			'editorIsRich' => $editorContext['rich_active'],
			'currentUserAvatar' => $userInfo['avatar']['url'],
			'isCurrentUserOwner' => $userInfo['id'] === $profileId,
			'canShowAddBuddyButton' => $this->canShowAddBuddyButton($profileId, $userInfo['id'], $wallUserSettings),
			UserSettingsEntity::ENABLE_BUDDIES_TAB => $wallUserSettings->getEnableBuddiesTab(),
			UserSettingsEntity::ABOUT_ME => !in_array($wallUserSettings->getAboutMe(), ['', '0'], true),
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
		if (in_array($profileId, $userInfo['buddies'] ?? [], true)) {
			return false;
		}

		// Check 3 & 4: Not in the user's wall ignore list or blocked from sending buddy requests
		if ($wallUserSettings === null) {
			$wallUserSettings = $this->userSettingsRepository->getById($profileId);
		}

		$blockList = $wallUserSettings->getBlockList();
		$isInBlockList = !empty($blockList) && in_array($userId, $blockList, true);

		// If in block list and wall owner has enabled "block buddy requests from ignored users"
		if ($isInBlockList && $wallUserSettings->getBlockBuddyRequests() !== 0) {
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

	public function hookProfilePopUp(&$profile_items): void
	{
		if (!$this->isEnable(SettingsEntity::MASTER)) {
			return;
		}

		$this->setLanguage(Breeze::NAME);

		$scriptUrl = $this->global(Breeze::SCRIPT_URL);
		$currentUserInfo = $this->global('user_info');
		$currentUserSettings = $this->getCurrentUserSettings();

		if ($currentUserSettings->getWall() !== 0 ||
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
		$forceWall = $this->getSetting(SettingsEntity::FORCE_WALL);
		$isWallEnable = $profileSettings->getWall() !== 0;
		$blockList = $profileSettings->getBlockList();

		if (!$isWallEnable && !empty($forceWall)) {
			return true;
		}

		if (!$isWallEnable) {
			return false;
		}

		if (!$this->isAllowedTo(PermissionsEnum::PROFILE_VIEW)) {
			return false;
		}

  return !($profileSettings->getKickIgnored() !== 0 &&
			$blockList !== [] &&
			in_array($userId, $blockList, true));
	}

	public function stalkingCheck(int $userStalkedId = 0): bool
	{
		$user_info = $this->global('user_info');

		if (empty($user_info['id'])) {
			return true;
		}

		$userStalkedSettings = $this->userSettingsRepository->getById($userStalkedId);
		$blockedList = $userStalkedSettings->getBlockList();
		$kickIgnored = $userStalkedSettings->getKickIgnored();

		if ($kickIgnored !== 0 && $blockedList !== []) {
			return in_array((int) $user_info['id'], $blockedList, true);
		}

		return false;
	}
}
