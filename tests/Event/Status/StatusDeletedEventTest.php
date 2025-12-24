<?php

declare(strict_types=1);

namespace Breeze\Event\Status;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\StoppableEventInterface;

#[AllowMockObjectsWithoutExpectations]
class StatusDeletedEventTest extends TestCase
{
	private StatusDeletedEvent $event;

	protected function setUp(): void
	{
		$this->event = new StatusDeletedEvent(123);
	}

	public function testImplementsStoppableEventInterface(): void
	{
		$this->assertInstanceOf(StoppableEventInterface::class, $this->event);
	}

	public function testGetStatusId(): void
	{
		$result = $this->event->getStatusId();
		$this->assertEquals(123, $result);
	}

	public function testConstructorWithDifferentValue(): void
	{
		$event = new StatusDeletedEvent(456);
		$this->assertEquals(456, $event->getStatusId());
	}

	public function testConstructorWithZeroValue(): void
	{
		$event = new StatusDeletedEvent(0);
		$this->assertEquals(0, $event->getStatusId());
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

	public function testGetStatusIdReturnsInt(): void
	{
		$result = $this->event->getStatusId();
		$this->assertIsInt($result);
	}

	public function testMultipleInstancesAreIndependent(): void
	{
		$event1 = new StatusDeletedEvent(100);
		$event2 = new StatusDeletedEvent(200);

		$this->assertEquals(100, $event1->getStatusId());
		$this->assertEquals(200, $event2->getStatusId());
	}

	public function testPropagationStoppedIsIndependentBetweenInstances(): void
	{
		$event1 = new StatusDeletedEvent(100);
		$event2 = new StatusDeletedEvent(200);

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

	public function testConstructorAcceptsStatusId(): void
	{
		$event = new StatusDeletedEvent(999);
		$this->assertInstanceOf(StatusDeletedEvent::class, $event);
		$this->assertEquals(999, $event->getStatusId());
	}

	public function testIsPropagationStoppedReturnsBoolean(): void
	{
		$result = $this->event->isPropagationStopped();
		$this->assertIsBool($result);
	}
}
