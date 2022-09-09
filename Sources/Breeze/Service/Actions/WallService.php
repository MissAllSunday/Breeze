<?php

declare(strict_types=1);


namespace Breeze\Service\Actions;

use Breeze\Breeze;
use Breeze\Entity\SettingsEntity;
use Breeze\Exceptions\InvalidStatusException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Service\UserService;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Components;
use Breeze\Util\Error;
use Breeze\Util\Permissions;

class WallService extends ActionsBaseService implements WallServiceInterface
{
	private array $usersToLoad = [];

	protected array $profileOwnerInfo = [];

	protected array $currentUserInfo = [];

	private array $profileOwnerSettings = [];

	public function __construct(
		private UserServiceInterface $userService,
		private StatusRepositoryInterface $statusRepository,
		private Components $components
	) {
	}

	public function init(array $subActions): void
	{
		if (!$this->isEnable(SettingsEntity::MASTER)) {
			Error::show('no_valid_action');
		}

		Permissions::isNotGuest($this->getText('error_no_access'));

		$this->setLanguage(Breeze::NAME);
		$this->setTemplate(Breeze::NAME);

		$this->currentUserInfo = $this->global('user_info');
		$this->profileOwnerInfo = $this->global('context')['member'];
		$this->profileOwnerSettings = $this->userService->getUserSettings($this->profileOwnerInfo['id']);

		$this->setUsersToLoad([
			$this->profileOwnerInfo['id'],
			$this->currentUserInfo['id'],
		]);

		$this->components->addJavaScriptVar(
			'profileOwnerSettings',
			$this->profileOwnerSettings
		);
		$this->components->addJavaScriptVar(
			'isCurrentUserOwner',
			$this->isCurrentUserOwner()
		);

		$this->components->addJavaScriptVar(
			Permissions::USE_MOOD,
			Permissions::isAllowedTo(Permissions::USE_MOOD)
		);

		$this->components->addJavaScriptVar('users', [
			'wallOwner' => $this->profileOwnerInfo['id'],
			'wallPoster' => $this->currentUserInfo['id'],
		]);
		$this->components->loadTxtVarsFor(['general', 'mood', 'like']);
		$this->components->loadComponents(
			['breeze-wall']
		);
		$this->components->loadCSSFile('breeze.css', [], 'smf_breeze');
	}

	public function loadUsers(): void
	{
		$usersToLoad = $this->getUsersToLoad();

		$loadedUsers = $this->userService->loadUsersInfo($usersToLoad);

		// We don't need all their info
		foreach ($loadedUsers as $userId => $userData) {
			$loadedUsers[$userId] = array_intersect($userData, UserService::MIN_INFO_KEYS);
		}
	}

	public function isAllowedToSeePage(bool $redirect = false): bool
	{
		$canSeePage = true;

		if (!$this->isCurrentUserOwner() && !$this->isEnable(SettingsEntity::FORCE_WALL)) {
			$canSeePage = false;
		} elseif (empty($this->profileOwnerSettings['wall'])) {
			$canSeePage = false;
		}

		if (!allowedTo('profile_view')) {
			$canSeePage = false;
		}

		if (!empty($this->profileOwnerSettings['kick_ignored']) && !empty($this->profileOwnerSettings['ignoredList'])) {
			$ownerIgnoredList = explode(',', $this->profileOwnerSettings['ignoredList']);

			if (in_array($this->currentUserInfo['id'], $ownerIgnoredList)) {
				$canSeePage = false;
			}
		}

		if (!$canSeePage && $redirect) {
			$this->redirect('action=profile;area=' .
				UserServiceInterface::LEGACY_AREA . ';u=' . $this->profileOwnerInfo['id']);
		}

		return true;
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function getStatus(int $userId): array
	{
		return $this->statusRepository->getByProfile($userId);
	}

	public function isCurrentUserOwner(): bool
	{
		if (!isset($this->currentUserInfo['id'])) {
			return false;
		}

		return $this->currentUserInfo['id'] === $this->profileOwnerInfo['id'];
	}

	public function getUsersToLoad(): array
	{
		return array_filter(array_unique($this->usersToLoad));
	}

	public function setUsersToLoad(array $usersToLoad): void
	{
		$this->usersToLoad = array_merge($usersToLoad, $this->usersToLoad);
	}

	public function getActionName(): string
	{
		return self::ACTION;
	}
}
