<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\InvalidStatusException;
use Breeze\Repository\StatusRepositoryInterface;

class StatusService extends BaseService implements StatusServiceInterface
{
	/**
	 * @var StatusRepositoryInterface
	 */
	private $statusRepository;

	/**
	 * @var CommentRepositoryInterface
	 */
	private $commentRepository;

	/**
	 * @var UserServiceInterface
	 */
	private $userService;

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

	/**
	 * @throws InvalidStatusException
	 */
	public function getByProfile(int $profileOwnerId = 0, int $start = 0): array
	{
		$profileStatus = $this->statusRepository->getByProfile($profileOwnerId, $start);
		$profileComments = $this->commentRepository->getByProfile($profileOwnerId);

		$userIds = array_unique(array_merge($profileStatus['usersIds'], $profileComments['usersIds']));
		$usersData = $this->userService->loadUsersInfo($userIds);

		foreach ($profileStatus['data'] as $statusId => &$status)
			$status['comments'] = $profileComments['data'][$statusId];

		return [
			'users' => $usersData,
			'status' => $profileStatus['data'],
		];
	}

	public function getById(int $statusId): array
	{
		return $this->statusRepository->getById($statusId);
	}

	public function saveAndGet(array $data): array
	{
		return [];
	}
}
