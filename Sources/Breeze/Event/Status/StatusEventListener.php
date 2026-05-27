<?php

declare(strict_types=1);

namespace Breeze\Event\Status;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Service\AlertServiceInterface;
use Breeze\Traits\TextTrait;
use Breeze\Util\Json;

class StatusEventListener
{
	use TextTrait;

	protected const string CONTENT_TYPE = Breeze::NAME . '_status';
	protected const string CONTENT_ACTION_CREATED = Breeze::PATTERN . 'created';
	protected const string CONTENT_ACTION_DELETED = Breeze::PATTERN . 'deleted';

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

		$this->alertService->send(AlertEntity::from([
			AlertEntity::ID_MEMBER => $wallId,
			AlertEntity::ID_MEMBER_STARTED => $userId,
			AlertEntity::CONTENT_TYPE => self::CONTENT_TYPE,
			AlertEntity::CONTENT_ID => $statusId,
			AlertEntity::CONTENT_ACTION => self::CONTENT_ACTION_CREATED,
			AlertEntity::EXTRA => Json::encode([
				'wall_id' => $wallId,
				'status_id' => $statusId,
			]),
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
