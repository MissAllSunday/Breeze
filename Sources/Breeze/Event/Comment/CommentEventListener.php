<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Event\EventAbstract;
use Breeze\Service\AlertServiceInterface;
use Breeze\Traits\TextTrait;
use Breeze\Util\Json;

class CommentEventListener
{
	use TextTrait;

	protected const string CONTENT_TYPE = Breeze::NAME . '_comment';

	public function __construct(
		protected readonly AlertServiceInterface $alertService
	) {
	}

	public function onCommentCreated(CommentCreatedEvent $event): void
	{
		$commentEntity = $event->getCommentEntity();
		$statusId = $commentEntity->getStatusId();
		$commentId = $commentEntity->getId();
		$userId = $commentEntity->getUserId();
		$statusOwnerId = $event->getStatusOwnerId();
		$wallOwnerId = $event->getWallId();
		$isSameUser = $userId === $statusOwnerId && $userId === $wallOwnerId;
		$shouldSendAlertToStatusOwner = $statusOwnerId !== $userId;
		$shouldSendAlertToWallOwner = $wallOwnerId !== $userId && $wallOwnerId !== $statusOwnerId;

		if ($isSameUser) {
			return;
		}

		// Alert for status owner
		if ($shouldSendAlertToStatusOwner) {
			$this->alertService->send(AlertEntity::from([
				AlertEntity::ID_MEMBER => $statusOwnerId,
				AlertEntity::ID_MEMBER_STARTED => $userId,
				AlertEntity::CONTENT_TYPE => self::CONTENT_TYPE,
				AlertEntity::CONTENT_ID => $commentId,
				AlertEntity::CONTENT_ACTION => EventAbstract::CONTENT_ACTION_CREATED . EventAbstract::STATUS_OWNER,
				AlertEntity::EXTRA => Json::encode([
					'status_id' => $event->getCommentEntity()->getStatusId(),
					'wall_id' => $wallOwnerId,
					'comment_id' => $commentId,
					'comment_owner_id' => $userId,
					'status_owner_id' => $statusOwnerId,
				]),
			]));
		}

		// If the comment is on someone else's wall, also notify the wall owner
		if ($shouldSendAlertToWallOwner) {
			$this->alertService->send(AlertEntity::from([
				AlertEntity::ID_MEMBER => $wallOwnerId,
				AlertEntity::ID_MEMBER_STARTED => $userId,
				AlertEntity::CONTENT_TYPE => self::CONTENT_TYPE,
				AlertEntity::CONTENT_ID => $commentId,
				AlertEntity::CONTENT_ACTION => EventAbstract::CONTENT_ACTION_CREATED . EventAbstract::WALL_OWNER,
				AlertEntity::EXTRA => Json::encode([
					'status_id' => $statusId,
					'wall_id' => $wallOwnerId,
					'comment_id' => $commentId,
					'comment_owner_id' => $userId,
					'status_owner_id' => $statusOwnerId,
				]),
			]));
		}
	}

	public function onCommentDeleted(CommentDeletedEvent $event): void
	{
		$userId = $event->getUserId();
		$shouldSendAlertToWallOwner = $event->getWallId() !== $userId;
		$shouldSendAlertToStatusOwner = $event->getStatusOwnerId() !== $userId;

		$isSameUser = $userId === $event->getWallId() && $userId === $event->getStatusOwnerId();

		if ($isSameUser) {
			return;
		}

		// Send alert to wall owner
		if ($shouldSendAlertToWallOwner) {
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
		if ($shouldSendAlertToStatusOwner) {
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
