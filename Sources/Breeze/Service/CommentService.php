<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\CommentEntity;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\InvalidCommentException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\ValidateGateway;

class CommentService  extends BaseService  implements CommentServiceInterface
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

	public function saveAndGet(array $data): array
	{
		try {
			$commentId = $this->commentRepository->save([
				CommentEntity::COLUMN_STATUS_ID => $data[CommentEntity::COLUMN_STATUS_ID],
				CommentEntity::COLUMN_STATUS_OWNER_ID => $data[CommentEntity::COLUMN_STATUS_OWNER_ID],
				CommentEntity::COLUMN_POSTER_ID => $data[CommentEntity::COLUMN_POSTER_ID],
				CommentEntity::COLUMN_PROFILE_ID => $data[CommentEntity::COLUMN_PROFILE_ID],
				CommentEntity::COLUMN_TIME => time(),
				CommentEntity::COLUMN_BODY => $data[CommentEntity::COLUMN_BODY],
				CommentEntity::COLUMN_LIKES => 0,
			]);

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
		return $this->commentRepository->getById($commentId);
	}
}
