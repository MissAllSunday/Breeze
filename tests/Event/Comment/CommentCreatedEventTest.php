<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\StatusEntity;
use Breeze\Event\EventAbstract;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class CommentCreatedEventTest extends TestCase
{
	private CommentEntity|MockObject $commentEntity;

	private StatusEntity|MockObject $statusEntity;

	private CommentCreatedEvent $event;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->commentEntity = $this->createStub(CommentEntity::class);
		$this->statusEntity = $this->createStub(StatusEntity::class);
		$this->statusEntity->method('getUserId')->willReturn(10);
		$this->statusEntity->method('getWallId')->willReturn(20);
		$this->event = new CommentCreatedEvent($this->commentEntity, $this->statusEntity);
	}

	public function testExtendsEventAbstract(): void
	{
		$this->assertInstanceOf(EventAbstract::class, $this->event);
	}

	public function testEventName(): void
	{
		$this->assertEquals(CommentCreatedEvent::class, $this->event->eventName());
	}

	public function testGetCommentEntity(): void
	{
		$result = $this->event->getCommentEntity();
		$this->assertSame($this->commentEntity, $result);
	}

	public function testGetStatusOwnerId(): void
	{
		$result = $this->event->getStatusOwnerId();
		$this->assertEquals(10, $result);
	}

	public function testGetWallId(): void
	{
		$result = $this->event->getWallId();
		$this->assertEquals(20, $result);
	}

	public function testConstructorWithDifferentValues(): void
	{
		$statusEntity = $this->createStub(StatusEntity::class);
		$statusEntity->method('getUserId')->willReturn(100);
		$statusEntity->method('getWallId')->willReturn(200);
		$event = new CommentCreatedEvent($this->commentEntity, $statusEntity);

		$this->assertEquals(100, $event->getStatusOwnerId());
		$this->assertEquals(200, $event->getWallId());
	}

	public function testConstructorWithZeroValues(): void
	{
		$statusEntity = $this->createStub(StatusEntity::class);
		$statusEntity->method('getUserId')->willReturn(0);
		$statusEntity->method('getWallId')->willReturn(0);
		$event = new CommentCreatedEvent($this->commentEntity, $statusEntity);

		$this->assertEquals(0, $event->getStatusOwnerId());
		$this->assertEquals(0, $event->getWallId());
	}

	public function testIsPropagationStoppedInitiallyFalse(): void
	{
		$this->assertFalse($this->event->isPropagationStopped());
	}

	public function testStopPropagation(): void
	{
		$this->event->stopPropagation();
		$this->assertTrue($this->event->isPropagationStopped());
	}

	public function testEventNameReturnsString(): void
	{
		$result = $this->event->eventName();
		$this->assertIsString($result);
	}

	public function testGetStatusOwnerIdReturnsInt(): void
	{
		$result = $this->event->getStatusOwnerId();
		$this->assertIsInt($result);
	}

	public function testGetWallIdReturnsInt(): void
	{
		$result = $this->event->getWallId();
		$this->assertIsInt($result);
	}

	public function testConstructorAcceptsCommentEntity(): void
	{
		$commentEntity = $this->createStub(CommentEntity::class);
		$statusEntity = $this->createStub(StatusEntity::class);
		$statusEntity->method('getUserId')->willReturn(5);
		$statusEntity->method('getWallId')->willReturn(10);
		$event = new CommentCreatedEvent($commentEntity, $statusEntity);

		$this->assertSame($commentEntity, $event->getCommentEntity());
	}

	public function testMultipleInstancesAreIndependent(): void
	{
		$statusEntity1 = $this->createStub(StatusEntity::class);
		$statusEntity1->method('getUserId')->willReturn(1);
		$statusEntity1->method('getWallId')->willReturn(2);
		$statusEntity2 = $this->createStub(StatusEntity::class);
		$statusEntity2->method('getUserId')->willReturn(3);
		$statusEntity2->method('getWallId')->willReturn(4);

		$event1 = new CommentCreatedEvent($this->commentEntity, $statusEntity1);
		$event2 = new CommentCreatedEvent($this->commentEntity, $statusEntity2);

		$this->assertEquals(1, $event1->getStatusOwnerId());
		$this->assertEquals(3, $event2->getStatusOwnerId());
		$this->assertEquals(2, $event1->getWallId());
		$this->assertEquals(4, $event2->getWallId());
	}

	public function testPropagationStoppedIsIndependentBetweenInstances(): void
	{
		$statusEntity1 = $this->createStub(StatusEntity::class);
		$statusEntity1->method('getUserId')->willReturn(1);
		$statusEntity1->method('getWallId')->willReturn(2);
		$statusEntity2 = $this->createStub(StatusEntity::class);
		$statusEntity2->method('getUserId')->willReturn(3);
		$statusEntity2->method('getWallId')->willReturn(4);

		$event1 = new CommentCreatedEvent($this->commentEntity, $statusEntity1);
		$event2 = new CommentCreatedEvent($this->commentEntity, $statusEntity2);

		$event1->stopPropagation();

		$this->assertTrue($event1->isPropagationStopped());
		$this->assertFalse($event2->isPropagationStopped());
	}
}
