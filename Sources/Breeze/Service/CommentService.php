<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\LikeEntity;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\InvalidCommentException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\ValidateGateway;

class CommentService extends BaseService implements CommentServiceInterface
{
	private StatusRepositoryInterface $statusRepository;

	private CommentRepositoryInterface $commentRepository;

	private UserServiceInterface $userService;

	private LikeServiceInterface $likeService;

	public function __construct(
		UserServiceInterface $userService,
		CommentRepositoryInterface $commentRepository,
		LikeServiceInterface $likeService
	) {
		$this->commentRepository = $commentRepository;
		$this->userService = $userService;
		$this->likeService = $likeService;
	}

	public function saveAndGet(array $data): array
	{
		try {
			$commentId = $this->commentRepository->save($data);

			$comment = $this->commentRepository->getById($commentId);
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
		$comments['data'] = $this->appendLikeData($comments['data']);

		return $comments;
	}

	public function getByProfile($profileOwnerId): array
	{
		$comments =  $this->commentRepository->getByProfile($profileOwnerId);

		foreach ($comments['data'] as $statusId => &$commentsById) {
			$commentsById = array_map(function ($comment): array {
				$comment['likesInfo'] = $this->likeService->buildLikeData(
					$comment[LikeEntity::IDENTIFIER . LikeEntity::TYPE],
					$comment[CommentEntity::ID],
					$comment[LikeEntity::IDENTIFIER . LikeEntity::ID_MEMBER],
				);

				return $comment;
			}, $commentsById);
		}

		$comments['data'] = $this->appendLikeData($comments['data']);

		return $comments;
	}

	protected function appendLikeData(array $commentsData) : array
	{
		foreach ($commentsData as $statusId => &$commentsById) {
			$commentsById = array_map(function ($comment): array {
				$comment['likesInfo'] = $this->likeService->buildLikeData(
					$comment[LikeEntity::IDENTIFIER . LikeEntity::TYPE],
					$comment[CommentEntity::ID],
					$comment[LikeEntity::IDENTIFIER . LikeEntity::ID_MEMBER],
				);

				return $comment;
			}, $commentsById);
		}

		return $commentsData;
	}
}
