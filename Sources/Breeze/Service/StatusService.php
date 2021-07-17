<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\InvalidStatusException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\ValidateGateway;

class StatusService extends BaseLikesService implements StatusServiceInterface
{
	private StatusRepositoryInterface $statusRepository;

	private UserServiceInterface $userService;

	private CommentServiceInterface $commentService;

	public function __construct(
		UserServiceInterface $userService,
		StatusRepositoryInterface $statusRepository,
		CommentServiceInterface $commentRepository,
		LikeServiceInterface $likeService
	) {
		$this->statusRepository = $statusRepository;
		$this->commentService = $commentRepository;
		$this->userService = $userService;

		parent::__construct($likeService);
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function getByProfile(int $profileOwnerId = 0, int $start = 0): array
	{
		$profileStatus = $this->statusRepository->getByProfile($profileOwnerId, $start);
		$profileComments = $this->commentService->getByProfile($profileOwnerId);

		$userIds = array_unique(array_merge($profileStatus['usersIds'], $profileComments['usersIds']));
		$usersData = $this->userService->loadUsersInfo($userIds);
		$profileStatus['data'] = $this->appendLikeData($profileStatus['data'], StatusEntity::ID);

		foreach ($profileStatus['data'] as $statusId => &$status) {
			$status['comments'] = $profileComments['data'][$statusId] ?? [];
		}

		return [
			'users' => $this->userService->loadUsersInfo($userIds),
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
				StatusEntity::CREATED_AT => time(),
				StatusEntity::LIKES => 0,
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

	/**
	 * @throws InvalidStatusException
	 */
	public function deleteById(int $commentId): bool
	{
		return $this->statusRepository->deleteById($commentId);
	}
}
