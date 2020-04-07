<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Entity\SettingsEntity;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Error;
use Breeze\Util\Permissions;

class WallService extends BaseService implements WallServiceInterface
{
	/**
	 * @var UserServiceInterface
	 */
	private $userService;

	/**
	 * @var StatusRepositoryInterface
	 */
	private $statusRepository;

	/**
	 * @var CommentRepositoryInterface
	 */
	private $commentRepository;

	private $usersToLoad = [];

	protected $profileOwnerInfo = [];

	protected $currentUserInfo = [];

	private $profileOwnerSettings = [];

	public function __construct(
		UserServiceInterface $userService,
		StatusRepositoryInterface $statusRepository,
		CommentRepositoryInterface $commentRepository
	)
	{
		$this->statusRepository = $statusRepository;
		$this->commentRepository = $commentRepository;
		$this->userService = $userService;
	}

	public function initPage(): void
	{
		if (!$this->enable(SettingsEntity::MASTER))
			Error::show('no_valid_action');

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
	}

	public function setSubActionContent(
		string $actionName,
		array $templateParams = [],
		string $smfTemplate = ''
	): void
	{
		if (empty($actionName))
			return;

		$this->loadCSS();
		$context = $this->global('context');
		$scriptUrl = $this->global('scripturl');

		if (!isset($context[Breeze::NAME]))
			$context[Breeze::NAME] = [];

		if (!empty($templateParams))
			$context = array_merge($context, $templateParams);

		$context['page_title'] = $this->getText('general_wall');
		$context['sub_template'] = !empty($smfTemplate) ?
			$smfTemplate : (self::ACTION . '_' . $actionName);
		$context['linktree'][] = [
			'url' => $scriptUrl . '?action=' . self::ACTION,
			'name' => $context['page_title'],
		];

		$this->setGlobal('context', $context);

		$this->userService->loadUsersInfo($this->getUsersToLoad());
	}

	public function isAllowedToSeePage(bool $redirect = false): bool
	{
		$canSeePage = true;

		if (!$this->isCurrentUserOwner() && !$this->enable(SettingsEntity::FORCE_WALL))
			$canSeePage = false;

		elseif (empty($this->profileOwnerSettings['wall']))
			$canSeePage = false;

		if (!allowedTo('profile_view'))
			$canSeePage = false;

		if (!empty($this->profileOwnerSettings['kick_ignored']) && !empty($this->profileOwnerSettings['ignoredList']))
		{
			$ownerIgnoredList = explode(',', $this->profileOwnerSettings['ignoredList']);

			if (in_array($this->currentUserInfo['id'], $ownerIgnoredList))
				$canSeePage = false;
		}

		if (!$canSeePage && $redirect)
			redirectexit('action=profile;area=' . UserService::LEGACY_AREA . ';u=' . $this->profileOwnerInfo['id']);

		return true;
	}

	public function getStatus(int $userId): array
	{
		return $this->statusRepository->getStatusByProfile($userId);
	}

	public function isCurrentUserOwner(): bool
	{
		if (!isset($this->currentUserInfo['id']))
			return false;

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
}
