<?php

declare(strict_types=1);

namespace Breeze\Event\Status;

use Breeze\Traits\TextTrait;

class StatusEventListener
{
	use TextTrait;

	public function onStatusCreated(StatusCreatedEvent $event): void
	{

		$statusId = $event->getStatusId();
		$userId = $event->getUserId();
		$wallId = $event->getWallId();


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
