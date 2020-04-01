<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Entity\StatusEntity;
use Breeze\Repository\RepositoryInterface;

class WallService extends BaseService implements ServiceInterface
{
	public const ACTION = 'breeze';

	private $usersToLoad = [];

	/**
	 * @var RepositoryInterface
	 */
	private $statusRepository;

	/**
	 * @var RepositoryInterface
	 */
	private $commentRepository;

	protected $profileOwnerInfo = [];

	protected $currentUserInfo = [];

	/**
	 * @var ServiceInterface
	 */
	private $userService;

	public function __construct(
		RepositoryInterface $repository,
		RepositoryInterface $statusRepository,
		RepositoryInterface $commentRepository,
		ServiceInterface $userService
	)
	{
		$this->statusRepository = $statusRepository;
		$this->commentRepository = $commentRepository;
		$this->userService = $userService;

		parent::__construct($repository);
	}

	public function initPage(): void
	{
		if (!$this->enable('master'))
			fatal_lang_error('Breeze_error_no_valid_action', false);

		is_not_guest($this->getText('error_no_access'));

		$this->setLanguage(Breeze::NAME);
		$this->setTemplate(Breeze::NAME);

		$this->currentUserInfo = $this->global('user_info');
		$this->profileOwnerInfo = $this->global('context')['member'];

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

		loadCSSFile('breeze.css', [], 'smf_breeze');
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

	public function isAllowedToSee(int $userId = 0, bool $redirect = false): bool
	{
		$canSee = true;
		$ownerId = $userId ? $userId : $this->profileOwnerInfo['id'];
		$ownerSettings = $this->userService->getUserSettings($ownerId);

		if (!$this->isCurrentUserOwner() && !$this->enable('force_enable'))
			$canSee = false;

		elseif (empty($ownerSettings['wall']))
			$canSee = false;

		if (!allowedTo('profile_view'))
			$canSee = false;

		if (!empty($ownerSettings['kick_ignored']) && !empty($ownerSettings['ignoredList']))
		{
			$ownerIgnoredList = explode(',', $ownerSettings['ignoredList']);

			if (in_array($this->currentUserInfo['id'], $ownerIgnoredList))
				$canSee = false;
		}

		if (!$canSee && $redirect)
			redirectexit('action=profile;area=' . UserService::LEGACY_AREA . ';u=' . $ownerId);

		return $canSee;
	}

	public function getStatus(int $userId): void
	{
		$status = $this->statusRepository->getStatusByProfile($userId);
	}

	protected function isCurrentUserOwner(): bool
	{
		if (!isset($this->currentUserInfo['id']))
			return false;

		return $this->currentUserInfo['id'] === $this->profileOwnerInfo['id'];
	}

	protected function getUsersToLoad(): array
	{
		return array_filter(array_unique($this->usersToLoad));
	}

	protected function setUsersToLoad(array $usersToLoad): void
	{
		$this->usersToLoad = array_merge($usersToLoad, $this->usersToLoad);
	}
}
