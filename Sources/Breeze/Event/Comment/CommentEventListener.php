<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Event\EventAbstract;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Service\AlertServiceInterface;
use Breeze\Traits\TextTrait;
use Breeze\Util\Json;

class CommentEventListener
{
	use TextTrait;

	protected const string CONTENT_TYPE = Breeze::NAME . '_comment';
	public function __construct(
		protected readonly AlertServiceInterface $alertService,
		protected readonly StatusRepositoryInterface $statusRepository
	) {
	}

	public function onCommentCreated(CommentCreatedEvent $event): void
	{
		$commentId = $event->getCommentId();
		$userId = $event->getUserId();
		$statusOwnerId = $event->getStatusOwnerId();
		$wallId = $event->getWallId();
		$isWallOwner = $wallId === $userId;
		$isStatusOwner = $statusOwnerId === $userId;
		$isCommentOwner = $commentId === $userId;

		// Alert for status owner
		if ($isStatusOwner && !$isWallOwner && !$isCommentOwner) {
			$this->alertService->send(AlertEntity::from([
				AlertEntity::ID_MEMBER => $statusOwnerId,
				AlertEntity::ID_MEMBER_STARTED => $userId,
				AlertEntity::CONTENT_TYPE => self::CONTENT_TYPE,
				AlertEntity::CONTENT_ID => $commentId,
				AlertEntity::CONTENT_ACTION => EventAbstract::CONTENT_ACTION_CREATED . EventAbstract::STATUS_OWNER,
				AlertEntity::EXTRA => Json::encode([
					'status_id' => $event->getStatusId(),
					'wall_id' => $wallId,
					'comment_id' => $commentId,
					'comment_owner_id' => $userId,
					'status_owner_id' => $statusOwnerId,
				]),
			]));
		}

		// If the comment is on someone else's wall, also notify the wall owner
		if ($wallId !== $statusOwnerId && $wallId !== $userId) {
			$this->alertService->send(AlertEntity::from([
				AlertEntity::ID_MEMBER => $wallId,
				AlertEntity::ID_MEMBER_STARTED => $userId,
				AlertEntity::CONTENT_TYPE => self::CONTENT_TYPE,
				AlertEntity::CONTENT_ID => $commentId,
				AlertEntity::CONTENT_ACTION => EventAbstract::CONTENT_ACTION_CREATED . EventAbstract::WALL_OWNER,
				AlertEntity::EXTRA => Json::encode([
					'status_id' => $event->getStatusId(),
					'status_owner_id' => $statusOwnerId,
				]),
			]));
		}
	}

	public function onCommentDeleted(CommentDeletedEvent $event): void
	{
		$isWallOwner = $event->getWallId() === $event->getUserId();
		$isStatusOwner = $event->getStatusOwnerId() === $event->getUserId();
		$isCommentOwner = $event->getCommentId() === $event->getUserId();

		// Send alert to wall owner
		if ($isWallOwner && !$isStatusOwner && !$isCommentOwner) {
			$this->alertService->send(AlertEntity::from([
				AlertEntity::ID_MEMBER => $event->getUserId(),
				AlertEntity::CONTENT_TYPE => self::CONTENT_TYPE,
				AlertEntity::CONTENT_ID => $event->getStatusId(),
				AlertEntity::CONTENT_ACTION => EventAbstract::CONTENT_ACTION_DELETED . EventAbstract::WALL_OWNER,
				AlertEntity::EXTRA => Json::encode([
					'status_id' => $event->getStatusId(),
					'wall_id' => $event->getWallId(),
					'user_id' => $event->getUserId(),
				]),
			]));
		}

		// Send alert to status owner
		if ($isStatusOwner && !$isWallOwner && !$isCommentOwner) {
			$this->alertService->send(AlertEntity::from([
				AlertEntity::ID_MEMBER => $event->getStatusOwnerId(),
				AlertEntity::CONTENT_TYPE => self::CONTENT_TYPE,
				AlertEntity::CONTENT_ID => $event->getStatusId(),
				AlertEntity::CONTENT_ACTION => EventAbstract::CONTENT_ACTION_DELETED . EventAbstract::STATUS_OWNER,
				AlertEntity::EXTRA => Json::encode([
					'status_id' => $event->getStatusId(),
					'wall_id' => $event->getWallId(),
					'user_id' => $event->getUserId(),
				]),
			]));
		}
	}
}
