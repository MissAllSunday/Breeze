<?php

declare(strict_types=1);

namespace Breeze\Event;

use Breeze\Event\Comment\CommentCreatedEvent;
use Breeze\Event\Comment\CommentDeletedEvent;
use Breeze\Event\Comment\CommentEventListener;
use Breeze\Event\Like\LikeCreatedEvent;
use Breeze\Event\Like\LikeEventListener;
use Breeze\Event\Status\StatusCreatedEvent;
use Breeze\Event\Status\StatusDeletedEvent;
use Breeze\Event\Status\StatusEventListener;
use League\Event\EventDispatcher;

class EventServiceProvider
{
	public function __construct(
		public EventDispatcher $eventDispatcher,
		protected StatusEventListener $statusEventListener,
		protected CommentEventListener $commentEventListener,
		protected LikeEventListener $likeEventListener
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

		// Register comment event listeners
		$this->eventDispatcher->subscribeTo(
			CommentCreatedEvent::class,
			[$this->commentEventListener, 'onCommentCreated']
		);

		$this->eventDispatcher->subscribeTo(
			CommentDeletedEvent::class,
			[$this->commentEventListener, 'onCommentDeleted']
		);

		// Register like event listeners
		$this->eventDispatcher->subscribeTo(
			LikeCreatedEvent::class,
			[$this->likeEventListener, 'onLikeCreated']
		);
	}
}
