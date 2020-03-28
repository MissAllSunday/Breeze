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

	public function __construct(
		RepositoryInterface $repository,
		RepositoryInterface $statusRepository,
		RepositoryInterface $commentRepository
	)
	{
		$this->statusRepository = $statusRepository;
		$this->commentRepository = $commentRepository;

		parent::__construct($repository);
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
	}

	public function loadUsersInfo(): void
	{
		$userIds = $this->getUsersToLoad();

		if (empty($userIds))
			return;

		$context = $this->global('context');
		$modSettings = $this->global('modSettings');

		if (!isset($context[Breeze::NAME]))
			$context[Breeze::NAME] = [];

		$context[Breeze::NAME]['users'] = [];
		$loadedIDs = loadMemberData($userIds);

		// Set the context var.
		foreach ($userIds as $userId)
		{
			if (!in_array($userId, $loadedIDs))
			{
				$context[Breeze::NAME]['users'][$userId] = [
					'link' => $this->getSmfText('guest_title'),
					'name' => $this->getSmfText('guest_title'),
					'avatar' => ['href' => $modSettings['avatar_url'] . '/default.png']
				];
				continue;
			}

			$context[Breeze::NAME]['users'][$userId] = loadMemberContext($userId, true);
		}
	}

	public function getStatus(int $userId): void
	{
		$status = $this->statusRepository->getStatusByProfile($userId);
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
