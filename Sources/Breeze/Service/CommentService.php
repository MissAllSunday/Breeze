<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\CommentEntity;
use Breeze\Exceptions\InvalidCommentException;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\ValidateGateway;

class CommentService extends BaseLikesService implements CommentServiceInterface
{
	private StatusRepositoryInterface $statusRepository;

	private CommentRepositoryInterface $commentRepository;

	private UserServiceInterface $userService;

	public function __construct(
		UserServiceInterface $userService,
		CommentRepositoryInterface $commentRepository,
		LikeServiceInterface $likeService
	) {
		$this->commentRepository = $commentRepository;
		$this->userService = $userService;

		parent::__construct($likeService);
	}

	public function saveAndGet(array $data): array
	{
		try {
			$commentId = $this->commentRepository->save($data);

			$comment = $this->getById($commentId);
		} catch (InvalidCommentException $e) {
			return [
				'type' => ValidateGateway::ERROR_TYPE,
				'message' => $e->getMessage(),
			];
		}

		return [
			'users' => $this->userService->loadUsersInfo(array_unique($comment['usersIds'])),
			'comments' => $comment['data'],
		];
	}

	/**
	 * @throws InvalidCommentException
	 */
	public function deleteById(int $commentId): bool
	{
		return $this->commentRepository->deleteById($commentId);
	}

	/**
	 * @throws InvalidCommentException
	 */
	public function getById(int $commentId): array
	{
		$comments = $this->commentRepository->getById($commentId);
		$comments['data'] = $this->appendLikeData($comments['data'], CommentEntity::ID);

		return $comments;
	}

	public function getByProfile($profileOwnerId): array
	{
		$comments =  $this->commentRepository->getByProfile($profileOwnerId);

		foreach ($comments['data'] as $statusId => &$commentsByStatusId) {
			$commentsByStatusId = $this->appendLikeData($commentsByStatusId, CommentEntity::ID);
		}

		return $comments;
	}

	public function getByStatusId(int $statusId): array
	{
		$comments =  $this->commentRepository->getByStatus([$statusId]);

		foreach ($comments['data'] as $statusId => &$commentsByStatusId) {
			$commentsByStatusId = $this->appendLikeData($commentsByStatusId, CommentEntity::ID);
		}

		return $comments;
	}
}
