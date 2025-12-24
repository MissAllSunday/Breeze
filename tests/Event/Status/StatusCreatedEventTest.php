<?php

declare(strict_types=1);

namespace Breeze\Event\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Event\EventAbstract;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class StatusCreatedEventTest extends TestCase
{
	private StatusEntity|MockObject $statusEntity;

	private StatusCreatedEvent $event;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->statusEntity = $this->createStub(StatusEntity::class);
		$this->statusEntity->method('getId')->willReturn(100);
		$this->statusEntity->method('getWallId')->willReturn(200);
		$this->statusEntity->method('getUserId')->willReturn(300);

		$this->event = new StatusCreatedEvent([$this->statusEntity]);
	}

	public function testExtendsEventAbstract(): void
	{
		$this->assertInstanceOf(EventAbstract::class, $this->event);
	}

	public function testGetStatus(): void
	{
		$result = $this->event->getStatus();
		$this->assertIsArray($result);
		$this->assertCount(1, $result);
		$this->assertSame($this->statusEntity, $result[0]);
	}

	public function testGetStatusId(): void
	{
		$result = $this->event->getStatusId();
		$this->assertEquals(100, $result);
	}

	public function testGetWallId(): void
	{
		$result = $this->event->getWallId();
		$this->assertEquals(200, $result);
	}

	public function testGetUserId(): void
	{
		$result = $this->event->getUserId();
		$this->assertEquals(300, $result);
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

	public function testConstructorWithDifferentValues(): void
	{
		$statusEntity = $this->createStub(StatusEntity::class);
		$statusEntity->method('getId')->willReturn(500);
		$statusEntity->method('getWallId')->willReturn(600);
		$statusEntity->method('getUserId')->willReturn(700);

		$event = new StatusCreatedEvent([$statusEntity]);

		$this->assertEquals(500, $event->getStatusId());
		$this->assertEquals(600, $event->getWallId());
		$this->assertEquals(700, $event->getUserId());
	}

	public function testGetStatusReturnsArray(): void
	{
		$result = $this->event->getStatus();
		$this->assertIsArray($result);
	}

	public function testGetStatusIdReturnsInt(): void
	{
		$result = $this->event->getStatusId();
		$this->assertIsInt($result);
	}

	public function testGetWallIdReturnsInt(): void
	{
		$result = $this->event->getWallId();
		$this->assertIsInt($result);
	}

	public function testGetUserIdReturnsInt(): void
	{
		$result = $this->event->getUserId();
		$this->assertIsInt($result);
	}

	public function testMultipleInstancesAreIndependent(): void
	{
		$statusEntity1 = $this->createStub(StatusEntity::class);
		$statusEntity1->method('getId')->willReturn(1);
		$statusEntity1->method('getWallId')->willReturn(2);
		$statusEntity1->method('getUserId')->willReturn(3);

		$statusEntity2 = $this->createStub(StatusEntity::class);
		$statusEntity2->method('getId')->willReturn(4);
		$statusEntity2->method('getWallId')->willReturn(5);
		$statusEntity2->method('getUserId')->willReturn(6);

		$event1 = new StatusCreatedEvent([$statusEntity1]);
		$event2 = new StatusCreatedEvent([$statusEntity2]);

		$this->assertEquals(1, $event1->getStatusId());
		$this->assertEquals(4, $event2->getStatusId());
	}

	public function testPropagationStoppedIsIndependentBetweenInstances(): void
	{
		$statusEntity1 = $this->createStub(StatusEntity::class);
		$statusEntity1->method('getId')->willReturn(1);
		$statusEntity1->method('getWallId')->willReturn(2);
		$statusEntity1->method('getUserId')->willReturn(3);

		$statusEntity2 = $this->createStub(StatusEntity::class);
		$statusEntity2->method('getId')->willReturn(4);
		$statusEntity2->method('getWallId')->willReturn(5);
		$statusEntity2->method('getUserId')->willReturn(6);

		$event1 = new StatusCreatedEvent([$statusEntity1]);
		$event2 = new StatusCreatedEvent([$statusEntity2]);

		$event1->stopPropagation();

		$this->assertTrue($event1->isPropagationStopped());
		$this->assertFalse($event2->isPropagationStopped());
	}

	public function testStopPropagationCanBeCalledMultipleTimes(): void
	{
		$this->event->stopPropagation();
		$this->assertTrue($this->event->isPropagationStopped());

		$this->event->stopPropagation();
		$this->assertTrue($this->event->isPropagationStopped());
	}
}
