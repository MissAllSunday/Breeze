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

	protected const CONTENT_TYPE = Breeze::NAME . '_comment';
	protected const CONTENT_ACTION_CREATED = Breeze::PATTERN . 'created';
	protected const CONTENT_ACTION_DELETED = Breeze::PATTERN . 'deleted';

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
		$this->alertService->send(new AlertEntity([
			AlertEntity::COLUMN_ID_MEMBER => $statusOwnerId,
			AlertEntity::COLUMN_ID_MEMBER_STARTED => $userId,
			AlertEntity::COLUMN_MEMBER_NAME => '', // To be filled by "handle" property
			AlertEntity::COLUMN_CONTENT_TYPE => self::CONTENT_TYPE,
			AlertEntity::COLUMN_CONTENT_ID => $commentId,
			AlertEntity::COLUMN_CONTENT_ACTION => self::CONTENT_ACTION_CREATED,
			AlertEntity::COLUMN_IS_READ => 0,
			AlertEntity::COLUMN_EXTRA => json_encode([
				'status_id' => $event->getStatusId(),
				'wall_id' => $wallId,
			]),
		]));

		// If the comment is on someone else's wall, also notify the wall owner
		if ($wallId !== $statusOwnerId && $wallId !== $userId) {
			$this->alertService->send(new AlertEntity([
				AlertEntity::COLUMN_ID_MEMBER => $wallId,
				AlertEntity::COLUMN_ID_MEMBER_STARTED => $userId,
				AlertEntity::COLUMN_MEMBER_NAME => '', // To be filled by "handle" property
				AlertEntity::COLUMN_CONTENT_TYPE => self::CONTENT_TYPE,
				AlertEntity::COLUMN_CONTENT_ID => $commentId,
				AlertEntity::COLUMN_CONTENT_ACTION => Breeze::PATTERN . 'profile_owner',
				AlertEntity::COLUMN_IS_READ => 0,
				AlertEntity::COLUMN_EXTRA => json_encode([
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
