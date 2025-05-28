<?php

declare(strict_types=1);

namespace Breeze\Event\Status;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Service\AlertServiceInterface;
use Breeze\Traits\TextTrait;

class StatusEventListener
{
	use TextTrait;

	protected const CONTENT_TYPE = Breeze::NAME . '_status';
	protected const CONTENT_ACTION_CREATED = Breeze::PATTERN . 'created';
	protected const CONTENT_ACTION_DELETED = Breeze::PATTERN . 'deleted';

	public function __construct(
		protected readonly AlertServiceInterface $alertService
	) {
	}

	public function onStatusCreated(StatusCreatedEvent $event): void
	{

		$statusId = $event->getStatusId();
		$userId = $event->getUserId();
		$wallId = $event->getWallId();

		// Don't do anything if the user is posting on their own wall
		if ($userId === $wallId) {
			return;
		}

		$this->alertService->send(new AlertEntity([
			AlertEntity::COLUMN_ID_MEMBER => $wallId,
			AlertEntity::COLUMN_ID_MEMBER_STARTED => $userId,
			AlertEntity::COLUMN_MEMBER_NAME => '', // To be filled by "handle" property
			AlertEntity::COLUMN_CONTENT_TYPE => self::CONTENT_TYPE,
			AlertEntity::COLUMN_CONTENT_ID => $statusId,
			AlertEntity::COLUMN_CONTENT_ACTION => self::CONTENT_ACTION_CREATED,
			AlertEntity::COLUMN_IS_READ => 0,
			AlertEntity::COLUMN_EXTRA => '',
		]));
	}

	public function onStatusDeleted(StatusDeletedEvent $event): void
	{
		// Example implementation - log the status deletion
		$statusId = $event->getStatusId();

		// Log the event or perform other actions
		// This is just a placeholder - implement actual logic as needed
		if (function_exists('log_error')) {
			log_error(sprintf(
				'Status deleted: ID=%d',
				$statusId
			));
		}
	}
}
