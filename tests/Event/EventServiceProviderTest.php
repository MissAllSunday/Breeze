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
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class EventServiceProviderTest extends TestCase
{
	private EventDispatcher|MockObject $eventDispatcher;

	private StatusEventListener|MockObject $statusEventListener;

	private CommentEventListener|MockObject $commentEventListener;

	private LikeEventListener|MockObject $likeEventListener;

	private EventServiceProvider $eventServiceProvider;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->eventDispatcher = $this->createMock(EventDispatcher::class);
		$this->statusEventListener = $this->createMock(StatusEventListener::class);
		$this->commentEventListener = $this->createMock(CommentEventListener::class);
		$this->likeEventListener = $this->createMock(LikeEventListener::class);
	}

	public function testConstructorRegistersListeners(): void
	{
		$this->eventDispatcher->expects($this->exactly(5))
			->method('subscribeTo')
			->willReturnCallback(function ($eventClass, $listener): void {
				$this->assertIsString($eventClass);
				$this->assertIsArray($listener);
				$this->assertCount(2, $listener);
			});

		$this->eventServiceProvider = new EventServiceProvider(
			$this->eventDispatcher,
			$this->statusEventListener,
			$this->commentEventListener,
			$this->likeEventListener
		);
	}

	public function testGetDispatcher(): void
	{
		$this->eventDispatcher->expects($this->exactly(5))
			->method('subscribeTo');

		$this->eventServiceProvider = new EventServiceProvider(
			$this->eventDispatcher,
			$this->statusEventListener,
			$this->commentEventListener,
			$this->likeEventListener
		);

		$result = $this->eventServiceProvider->getDispatcher();
		$this->assertSame($this->eventDispatcher, $result);
	}

	public function testRegistersStatusCreatedEventListener(): void
	{
		$this->eventDispatcher->expects($this->exactly(5))
			->method('subscribeTo')
			->willReturnCallback(function ($eventClass, $listener): void {
				if ($eventClass === StatusCreatedEvent::class) {
					$this->assertEquals([$this->statusEventListener, 'onStatusCreated'], $listener);
				}
			});

		$this->eventServiceProvider = new EventServiceProvider(
			$this->eventDispatcher,
			$this->statusEventListener,
			$this->commentEventListener,
			$this->likeEventListener
		);
	}

	public function testRegistersStatusDeletedEventListener(): void
	{
		$this->eventDispatcher->expects($this->exactly(5))
			->method('subscribeTo')
			->willReturnCallback(function ($eventClass, $listener): void {
				if ($eventClass === StatusDeletedEvent::class) {
					$this->assertEquals([$this->statusEventListener, 'onStatusDeleted'], $listener);
				}
			});

		$this->eventServiceProvider = new EventServiceProvider(
			$this->eventDispatcher,
			$this->statusEventListener,
			$this->commentEventListener,
			$this->likeEventListener
		);
	}

	public function testRegistersCommentCreatedEventListener(): void
	{
		$this->eventDispatcher->expects($this->exactly(5))
			->method('subscribeTo')
			->willReturnCallback(function ($eventClass, $listener): void {
				if ($eventClass === CommentCreatedEvent::class) {
					$this->assertEquals([$this->commentEventListener, 'onCommentCreated'], $listener);
				}
			});

		$this->eventServiceProvider = new EventServiceProvider(
			$this->eventDispatcher,
			$this->statusEventListener,
			$this->commentEventListener,
			$this->likeEventListener
		);
	}

	public function testRegistersCommentDeletedEventListener(): void
	{
		$this->eventDispatcher->expects($this->exactly(5))
			->method('subscribeTo')
			->willReturnCallback(function ($eventClass, $listener): void {
				if ($eventClass === CommentDeletedEvent::class) {
					$this->assertEquals([$this->commentEventListener, 'onCommentDeleted'], $listener);
				}
			});

		$this->eventServiceProvider = new EventServiceProvider(
			$this->eventDispatcher,
			$this->statusEventListener,
			$this->commentEventListener,
			$this->likeEventListener
		);
	}

	public function testRegistersLikeCreatedEventListener(): void
	{
		$this->eventDispatcher->expects($this->exactly(5))
			->method('subscribeTo')
			->willReturnCallback(function ($eventClass, $listener): void {
				if ($eventClass === LikeCreatedEvent::class) {
					$this->assertEquals([$this->likeEventListener, 'onLikeCreated'], $listener);
				}
			});

		$this->eventServiceProvider = new EventServiceProvider(
			$this->eventDispatcher,
			$this->statusEventListener,
			$this->commentEventListener,
			$this->likeEventListener
		);
	}
}
