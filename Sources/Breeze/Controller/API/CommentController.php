<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\CommentEntity;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\InvalidCommentException;
use Breeze\Util\Response;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;

class CommentController extends ApiBaseController
{
	public const ACTION_POST_COMMENT = 'postComment';
	public const ACTION_DELETE = 'deleteComment';
	public const SUB_ACTIONS = [
		self::ACTION_POST_COMMENT,
		self::ACTION_DELETE,
	];

	public function __construct(
		protected CommentRepositoryInterface $commentRepository,
		protected ValidateActionsInterface $validateActions,
		protected Response $response
	) {
		parent::__construct($validateActions, $response);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function postComment(): void
	{
		try {
			$commentId = $this->commentRepository->save($this->data);
			$comment = $this->commentRepository->getById($commentId);
			$comment[$commentId]['isNew'] = true;

			$this->response->success(
				'published_comment',
				$comment,
				Response::CREATED
			);
		} catch (InvalidCommentException $invalidCommentException) {
			$this->response->error($invalidCommentException->getMessage(), $invalidCommentException->getResponseCode());
		}
	}

	public function deleteComment(): void
	{
		try {
			$this->commentRepository->deleteById($this->data[CommentEntity::ID]);

			$this->response->success('deleted_comment');
		} catch (InvalidCommentException $invalidCommentException) {
			$this->response->error($invalidCommentException->getMessage(), $invalidCommentException->getResponseCode());
		}
	}
}
