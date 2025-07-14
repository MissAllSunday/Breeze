<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Service\AlertServiceInterface;
use Breeze\Traits\TextTrait;

class CommentEventListener
{
	use TextTrait;

	protected const string CONTENT_TYPE = Breeze::NAME . '_comment';
	protected const string CONTENT_ACTION_CREATED = Breeze::PATTERN . 'created';
	protected const string CONTENT_ACTION_DELETED = Breeze::PATTERN . 'deleted';

	public function __construct(
		protected readonly AlertServiceInterface $alertService
	) {
	}

	public function onCommentCreated(CommentCreatedEvent $event): void
	{
		$commentId = $event->getCommentId();
		$userId = $event->getUserId();
		$statusOwnerId = $event->getStatusOwnerId();
		$wallId = $event->getWallId();

		// Don't send alert if user is commenting on their own status
		if ($userId === $statusOwnerId) {
			return;
		}

		// Alert for status owner
		$this->alertService->send(AlertEntity::from([
			AlertEntity::ID_MEMBER => $statusOwnerId,
			AlertEntity::ID_MEMBER_STARTED => $userId,
			AlertEntity::CONTENT_TYPE => self::CONTENT_TYPE,
			AlertEntity::CONTENT_ID => $commentId,
			AlertEntity::CONTENT_ACTION => self::CONTENT_ACTION_CREATED,
			AlertEntity::EXTRA => json_encode([
				'status_id' => $event->getStatusId(),
				'wall_id' => $wallId,
			]),
		]));

		// If the comment is on someone else's wall, also notify the wall owner
		if ($wallId !== $statusOwnerId && $wallId !== $userId) {
			$this->alertService->send(AlertEntity::from([
				AlertEntity::ID_MEMBER => $wallId,
				AlertEntity::ID_MEMBER_STARTED => $userId,
				AlertEntity::CONTENT_TYPE => self::CONTENT_TYPE,
				AlertEntity::CONTENT_ID => $commentId,
				AlertEntity::CONTENT_ACTION => Breeze::PATTERN . 'profile_owner',
				AlertEntity::EXTRA => json_encode([
					'status_id' => $event->getStatusId(),
					'status_owner_id' => $statusOwnerId,
				]),
			]));
		}
	}

	public function onCommentDeleted(CommentDeletedEvent $event): void
	{
		// Example implementation - log the comment deletion
		$commentId = $event->getCommentId();

		// Log the event or perform other actions
		if (function_exists('log_error')) {
			log_error(sprintf(
				'Comment deleted: ID=%d',
				$commentId
			));
		}
	}
}
