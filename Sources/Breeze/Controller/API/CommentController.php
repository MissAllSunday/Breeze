<?php

declare(strict_types=1);

namespace Breeze\Controller\API;

use Breeze\Entity\CommentEntity;
use Breeze\Repository\InvalidCommentException;
use Breeze\Service\CommentServiceInterface;
use Breeze\Service\SecurityServiceInterface;
use Breeze\Util\ResponseInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;

class CommentController extends ApiBaseController
{
	public const string ACTION_POST_COMMENT = 'postComment';
	public const string ACTION_DELETE = 'deleteComment';

	public const array SUB_ACTIONS = [
		self::ACTION_POST_COMMENT,
		self::ACTION_DELETE,
	];

	public function __construct(
		protected CommentServiceInterface $commentService,
		protected ValidateActionsInterface $validateActions,
		protected ResponseInterface $response,
		protected SecurityServiceInterface $security
	) {
		parent::__construct($validateActions, $response, $security);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function getMutatingActions(): array
	{
		return [
			self::ACTION_POST_COMMENT,
			self::ACTION_DELETE,
		];
	}

	public function postComment(): void
	{
		try {
			$commentEntities = $this->commentService->save($this->data);

			$this->response->success(
				'published_comment',
				$commentEntities,
				ResponseInterface::CREATED
			);
		} catch (InvalidCommentException $invalidCommentException) {
			$this->response->error($invalidCommentException->getMessage(), $invalidCommentException->getResponseCode());
		}
	}

	public function deleteComment(): void
	{
		try {
			$this->commentService->deleteById($this->data[CommentEntity::ID]);

			$this->response->success('deleted_comment');
		} catch (DataNotFoundException $dataNotFoundException) {
			$this->response->error($dataNotFoundException->getMessage());
		}
	}
}
