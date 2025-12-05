<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\CommentEntity;
use Breeze\Event\Comment\CommentCreatedEvent;
use Breeze\Event\EventServiceProvider;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\InvalidCommentException;
use Breeze\Util\Response;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;

class CommentController extends ApiBaseController
{
	public const string ACTION_POST_COMMENT = 'postComment';
	public const string ACTION_DELETE = 'deleteComment';

	/** @var string[] */
	public const array SUB_ACTIONS = [
		self::ACTION_POST_COMMENT,
		self::ACTION_DELETE,
	];

	public function __construct(
		protected CommentRepositoryInterface $commentRepository,
		protected ValidateActionsInterface $validateActions,
		protected Response $response,
		protected EventServiceProvider $eventServiceProvider
	) {
		parent::__construct($validateActions, $response, $eventServiceProvider);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function postComment(): void
	{
		try {
			$commentEntities = $this->commentRepository->insert(CommentEntity::from($this->data));

			$this->response->success(
				'published_comment',
				$commentEntities,
				Response::CREATED
			);


			foreach ($commentEntities as $commentEntity) {
				$this->eventDispatch(CommentCreatedEvent::class, $commentEntity, );
			}
		} catch (InvalidCommentException $invalidCommentException) {
			$this->response->error($invalidCommentException->getMessage(), $invalidCommentException->getResponseCode());
		} catch (DataNotFoundException $e) {
		}
	}

	public function deleteComment(): void
	{
		try {
			$this->commentRepository->deleteById($this->data[CommentEntity::ID]);

			$this->response->success('deleted_comment', [], Response::NO_CONTENT);
		} catch (DataNotFoundException $dataNotFoundException) {
			$this->response->error($dataNotFoundException->getMessage());
		}
	}
}
