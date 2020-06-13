<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\CommentEntity;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\Validations\PostComment;

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
		$commentId = $this->commentRepository->save([
			CommentEntity::COLUMN_STATUS_ID => $data[PostComment::PARAM_STATUS_ID],
			CommentEntity::COLUMN_STATUS_OWNER_ID => $data[PostComment::PARAM_STATUS_OWNER_ID],
			CommentEntity::COLUMN_POSTER_ID => $data[PostComment::PARAM_POSTER_ID],
			CommentEntity::COLUMN_PROFILE_ID => $data[PostComment::PARAM_PROFILE_OWNER_ID],
			CommentEntity::COLUMN_TIME => time(),
			CommentEntity::COLUMN_BODY => $data[PostComment::PARAM_BODY],
			CommentEntity::COLUMN_LIKES => 0,
		]);

		$comment = $this->commentRepository->getById($commentId);

		return [
			'users' => $this->userService->loadUsersInfo(array_unique($comment['usersIds'])),
			'comments' => $comment['data'],
		];
	}
}
