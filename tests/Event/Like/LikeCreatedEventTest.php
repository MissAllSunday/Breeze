<?php

declare(strict_types=1);

namespace Breeze\Event\Like;

use Breeze\Entity\LikeEntity;
use Breeze\Event\EventAbstract;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class LikeCreatedEventTest extends TestCase
{
	private LikeEntity|MockObject $likeEntity;

	private LikeCreatedEvent $event;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->likeEntity = $this->createStub(LikeEntity::class);
		$this->event = new LikeCreatedEvent($this->likeEntity);
	}

	public function testExtendsEventAbstract(): void
	{
		$this->assertInstanceOf(EventAbstract::class, $this->event);
	}

	public function testGetLikeEntity(): void
	{
		$result = $this->event->getLikeEntity();
		$this->assertSame($this->likeEntity, $result);
	}

	public function testConstructorAcceptsLikeEntity(): void
	{
		$likeEntity = $this->createStub(LikeEntity::class);
		$event = new LikeCreatedEvent($likeEntity);

		$this->assertSame($likeEntity, $event->getLikeEntity());
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

	public function testMultipleInstancesAreIndependent(): void
	{
		$likeEntity1 = $this->createStub(LikeEntity::class);
		$likeEntity2 = $this->createStub(LikeEntity::class);

		$event1 = new LikeCreatedEvent($likeEntity1);
		$event2 = new LikeCreatedEvent($likeEntity2);

		$this->assertSame($likeEntity1, $event1->getLikeEntity());
		$this->assertSame($likeEntity2, $event2->getLikeEntity());
		$this->assertNotSame($event1->getLikeEntity(), $event2->getLikeEntity());
	}

	public function testPropagationStoppedIsIndependentBetweenInstances(): void
	{
		$likeEntity1 = $this->createStub(LikeEntity::class);
		$likeEntity2 = $this->createStub(LikeEntity::class);

		$event1 = new LikeCreatedEvent($likeEntity1);
		$event2 = new LikeCreatedEvent($likeEntity2);

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

	public function testGetLikeEntityReturnsLikeEntity(): void
	{
		$result = $this->event->getLikeEntity();
		$this->assertInstanceOf(LikeEntity::class, $result);
	}

	public function testConstructorStoresLikeEntity(): void
	{
		$likeEntity = $this->createStub(LikeEntity::class);
		$likeEntity->method('getContentId')->willReturn(123);

		$event = new LikeCreatedEvent($likeEntity);

		$this->assertEquals(123, $event->getLikeEntity()->getContentId());
	}
}
