<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\CommentEntity;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\ValidateComment;

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
			CommentEntity::COLUMN_STATUS_ID => $data[ValidateComment::PARAM_STATUS_ID],
			CommentEntity::COLUMN_STATUS_OWNER_ID => $data[ValidateComment::PARAM_STATUS_OWNER_ID],
			CommentEntity::COLUMN_POSTER_ID => $data[ValidateComment::PARAM_POSTER_ID],
			CommentEntity::COLUMN_PROFILE_ID => $data[ValidateComment::PARAM_PROFILE_OWNER_ID],
			CommentEntity::COLUMN_TIME => time(),
			CommentEntity::COLUMN_BODY => $data[ValidateComment::PARAM_BODY],
			CommentEntity::COLUMN_LIKES => 0,
		]);

		return $this->commentRepository->getById($commentId);
	}
}
