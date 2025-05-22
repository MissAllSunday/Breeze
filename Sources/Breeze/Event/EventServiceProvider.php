<?php

declare(strict_types=1);

namespace Breeze\Event;

use Breeze\Event\Status\StatusCreatedEvent;
use Breeze\Event\Status\StatusDeletedEvent;
use Breeze\Event\Status\StatusEventListener;
use League\Event\EventDispatcher;

class EventServiceProvider
{
	public function __construct(
		public  EventDispatcher $eventDispatcher,
		protected  StatusEventListener $statusEventListener
	) {
		$this->registerListeners();
	}

	public function getDispatcher(): EventDispatcher
	{
		return $this->eventDispatcher;
	}

	protected function registerListeners(): void
	{
		// Register status event listeners
		$this->eventDispatcher->subscribeTo(
			StatusCreatedEvent::class,
			[$this->statusEventListener, 'onStatusCreated']
		);

		$this->eventDispatcher->subscribeTo(
			StatusDeletedEvent::class,
			[$this->statusEventListener, 'onStatusDeleted']
		);
	}
}
