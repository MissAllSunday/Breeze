<?php

declare(strict_types=1);

namespace Breeze\Event\Like;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Service\AlertServiceInterface;
use Breeze\Traits\TextTrait;

class LikeEventListener
{
	use TextTrait;

	protected const string CONTENT_TYPE = Breeze::NAME . '_like';
	protected const string CONTENT_ACTION_CREATED = Breeze::PATTERN . 'created';

	public function __construct(
		protected readonly AlertServiceInterface $alertService
	) {
	}

	public function onLikeCreated(LikeCreatedEvent $event): void
	{
		$likeId = $event->getLikeId();
		$userId = $event->getUserId();
		$contentOwnerId = $event->getContentOwnerId();
		$contentType = $event->getContentType();
		$contentId = $event->getContentId();

		// Don't send alert if user is liking their own content
		if ($userId === $contentOwnerId) {
			return;
		}

		$this->alertService->send(new AlertEntity([
			AlertEntity::COLUMN_ID_MEMBER => $contentOwnerId,
			AlertEntity::COLUMN_ID_MEMBER_STARTED => $userId,
			AlertEntity::COLUMN_CONTENT_TYPE => self::CONTENT_TYPE,
			AlertEntity::COLUMN_CONTENT_ID => $likeId,
			AlertEntity::COLUMN_CONTENT_ACTION => self::CONTENT_ACTION_CREATED,
			AlertEntity::COLUMN_IS_READ => 0,
			AlertEntity::COLUMN_EXTRA => [
				'content_id' => $contentId,
				'content_type' => $contentType,
			],
		]));
	}
}
