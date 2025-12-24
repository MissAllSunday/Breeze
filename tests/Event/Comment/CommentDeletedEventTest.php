<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use League\Event\HasEventName;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class CommentDeletedEventTest extends TestCase
{
	private CommentDeletedEvent $event;

	protected function setUp(): void
	{
		$this->event = new CommentDeletedEvent(1, 2, 3, 4, 5);
	}

	public function testImplementsHasEventName(): void
	{
		$this->assertInstanceOf(HasEventName::class, $this->event);
	}

	public function testEventName(): void
	{
		$this->assertEquals(CommentDeletedEvent::class, $this->event->eventName());
	}

	public function testGetCommentId(): void
	{
		$result = $this->event->getCommentId();
		$this->assertEquals(1, $result);
	}

	public function testGetUserId(): void
	{
		$result = $this->event->getUserId();
		$this->assertEquals(2, $result);
	}

	public function testGetStatusId(): void
	{
		$result = $this->event->getStatusId();
		$this->assertEquals(3, $result);
	}

	public function testGetStatusOwnerId(): void
	{
		$result = $this->event->getStatusOwnerId();
		$this->assertEquals(4, $result);
	}

	public function testGetWallId(): void
	{
		$result = $this->event->getWallId();
		$this->assertEquals(5, $result);
	}

	public function testConstructorWithDifferentValues(): void
	{
		$event = new CommentDeletedEvent(10, 20, 30, 40, 50);

		$this->assertEquals(10, $event->getCommentId());
		$this->assertEquals(20, $event->getUserId());
		$this->assertEquals(30, $event->getStatusId());
		$this->assertEquals(40, $event->getStatusOwnerId());
		$this->assertEquals(50, $event->getWallId());
	}

	public function testConstructorWithZeroValues(): void
	{
		$event = new CommentDeletedEvent(0, 0, 0, 0, 0);

		$this->assertEquals(0, $event->getCommentId());
		$this->assertEquals(0, $event->getUserId());
		$this->assertEquals(0, $event->getStatusId());
		$this->assertEquals(0, $event->getStatusOwnerId());
		$this->assertEquals(0, $event->getWallId());
	}

	public function testEventNameReturnsString(): void
	{
		$result = $this->event->eventName();
		$this->assertIsString($result);
	}

	public function testAllGettersReturnInt(): void
	{
		$this->assertIsInt($this->event->getCommentId());
		$this->assertIsInt($this->event->getUserId());
		$this->assertIsInt($this->event->getStatusId());
		$this->assertIsInt($this->event->getStatusOwnerId());
		$this->assertIsInt($this->event->getWallId());
	}

	public function testMultipleInstancesAreIndependent(): void
	{
		$event1 = new CommentDeletedEvent(1, 2, 3, 4, 5);
		$event2 = new CommentDeletedEvent(6, 7, 8, 9, 10);

		$this->assertEquals(1, $event1->getCommentId());
		$this->assertEquals(6, $event2->getCommentId());
		$this->assertEquals(2, $event1->getUserId());
		$this->assertEquals(7, $event2->getUserId());
	}

	public function testConstructorAcceptsAllParameters(): void
	{
		$event = new CommentDeletedEvent(100, 200, 300, 400, 500);

		$this->assertInstanceOf(CommentDeletedEvent::class, $event);
		$this->assertEquals(100, $event->getCommentId());
		$this->assertEquals(200, $event->getUserId());
		$this->assertEquals(300, $event->getStatusId());
		$this->assertEquals(400, $event->getStatusOwnerId());
		$this->assertEquals(500, $event->getWallId());
	}

	public function testEventNameIsConsistent(): void
	{
		$event1 = new CommentDeletedEvent(1, 2, 3, 4, 5);
		$event2 = new CommentDeletedEvent(6, 7, 8, 9, 10);

		$this->assertEquals($event1->eventName(), $event2->eventName());
	}
}
