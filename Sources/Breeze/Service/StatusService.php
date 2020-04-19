<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\CommentRepositoryInterface;
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

	public function getByProfile(int $profileOwnerId = 0, int $start = 0): array
	{
		$profileStatus = $this->statusRepository->getStatusByProfile($profileOwnerId, $start);
		$profileComments = $this->commentRepository->getStatusByProfile($profileOwnerId);

		$userIds = array_unique(array_merge($profileStatus['usersIds'], $profileComments['usersIds']));
		$usersData = $this->userService->loadUsersInfo($userIds);

		return [
			'users' => $usersData,
			'status' => $profileStatus['data'],
			'comments' => $profileComments['data'],
		];
	}
}
