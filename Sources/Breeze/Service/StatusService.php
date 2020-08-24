<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\InvalidStatusException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\ValidateGateway;

class StatusService extends BaseService implements StatusServiceInterface
{
	private StatusRepositoryInterface $statusRepository;

	private CommentRepositoryInterface $commentRepository;

	private UserServiceInterface $userService;

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
			$status['comments'] = $profileComments['data'][$statusId] ?? [];

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
		try {
			$statusId = $this->statusRepository->save(array_merge($data, [
				StatusEntity::COLUMN_TIME => time(),
				StatusEntity::COLUMN_LIKES => 0,
			]));

			$newStatus = $this->statusRepository->getById($statusId);

		} catch (InvalidStatusException $e) {
			return [
				'type' => ValidateGateway::ERROR_TYPE,
				'message' => $e->getMessage(),
			];
		}

		return [
			'users' => $this->userService->loadUsersInfo(array_unique($newStatus['usersIds'])),
			'status' => $newStatus['data'],
		];
	}
}
