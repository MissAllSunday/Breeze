<?php

declare(strict_types=1);

namespace Breeze\Event\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Fixtures\StatusFixtures;
use Breeze\Service\AlertServiceInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class StatusEventListenerTest extends TestCase
{
	private AlertServiceInterface|MockObject $alertService;

	private StatusEventListener $listener;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->alertService = $this->createMock(AlertServiceInterface::class);
		$this->listener = new StatusEventListener($this->alertService);
	}

	/**
	 * @throws Exception
	 */
	public function testOnStatusCreatedWhenUserPostsOnOwnWall(): void
	{
		$statusEntity = $this->createStub(StatusEntity::class);
		$statusEntity->method('getId')->willReturn(100);
		$statusEntity->method('getUserId')->willReturn(5);
		$statusEntity->method('getWallId')->willReturn(5);

		$event = new StatusCreatedEvent([$statusEntity]);

		// Should not send alert when user posts on their own wall
		$this->alertService->expects($this->never())
			->method('send');

		$this->listener->onStatusCreated($event);
	}

	/**
	 * @throws Exception
	 */
	public function testOnStatusCreatedWhenUserPostsOnDifferentWall(): void
	{
		$statusEntity = $this->createStub(StatusEntity::class);
		$statusEntity->method('getId')->willReturn(200);
		$statusEntity->method('getUserId')->willReturn(10);
		$statusEntity->method('getWallId')->willReturn(20);

		$event = new StatusCreatedEvent([$statusEntity]);

		// Should send alert when user posts on someone else's wall
		$this->alertService->expects($this->once())
			->method('send');

		$this->listener->onStatusCreated($event);
	}

	/**
	 * @throws Exception
	 */
	public function testOnStatusCreatedSendsAlertToWallOwner(): void
	{
		$statusEntity = $this->createStub(StatusEntity::class);
		$statusEntity->method('getId')->willReturn(300);
		$statusEntity->method('getUserId')->willReturn(15);
		$statusEntity->method('getWallId')->willReturn(25);

		$event = new StatusCreatedEvent([$statusEntity]);

		$this->alertService->expects($this->once())
			->method('send')
			->with($this->callback(function ($alert) {
				return $alert->getIdMember() === 25;
			}));

		$this->listener->onStatusCreated($event);
	}

	/**
	 * @throws Exception
	 */
	public function testOnStatusCreatedStoresWallIdInExtra(): void
	{
		$statusEntity = $this->createStub(StatusEntity::class);
		$statusEntity->method('getId')->willReturn(32);
		$statusEntity->method('getUserId')->willReturn(1);
		$statusEntity->method('getWallId')->willReturn(2);

		$event = new StatusCreatedEvent([$statusEntity]);

		$this->alertService->expects($this->once())
			->method('send')
			->with($this->callback(function ($alert) {
				$extra = $alert->getExtra();

				return isset($extra['wall_id']) && $extra['wall_id'] === 2
					&& isset($extra['status_id']) && $extra['status_id'] === 32;
			}));

		$this->listener->onStatusCreated($event);
	}

	/**
	 * @throws Exception
	 */
	public function testOnStatusCreatedUsesCorrectContentType(): void
	{
		$statusEntity = $this->createStub(StatusEntity::class);
		$statusEntity->method('getId')->willReturn(400);
		$statusEntity->method('getUserId')->willReturn(30);
		$statusEntity->method('getWallId')->willReturn(40);

		$event = new StatusCreatedEvent([$statusEntity]);

		$this->alertService->expects($this->once())
			->method('send')
			->with($this->callback(function ($alert) {
				return $alert->getContentType() === 'Breeze_status';
			}));

		$this->listener->onStatusCreated($event);
	}

	/**
	 * @throws Exception
	 */
	public function testOnStatusCreatedUsesCorrectContentAction(): void
	{
		$statusEntity = StatusEntity::from(StatusFixtures::basic());

		$event = new StatusCreatedEvent([$statusEntity]);

		$this->alertService->expects($this->once())
			->method('send')
			->with($this->callback(function ($alert) {
				return $alert->getContentAction() === 'Breeze_created';
			}));

		$this->listener->onStatusCreated($event);
	}

	#[AllowMockObjectsWithoutExpectations]
	public function testOnStatusDeletedLogsEvent(): void
	{
		$event = new StatusDeletedEvent(123);

		// This method just logs, so we just ensure it doesn't throw
		$this->listener->onStatusDeleted($event);

		$this->expectNotToPerformAssertions();
	}

	#[AllowMockObjectsWithoutExpectations]
	public function testOnStatusDeletedWithDifferentStatusId(): void
	{
		$event = new StatusDeletedEvent(999);

		// This method just logs, so we just ensure it doesn't throw
		$this->listener->onStatusDeleted($event);

		$this->expectNotToPerformAssertions();
	}

	/**
	 * @throws Exception
	 */
	public function testOnStatusCreatedWithMultipleDifferentScenarios(): void
	{
		// Scenario 1: User posts on own wall - no alert
		$statusEntity1 = $this->createStub(StatusEntity::class);
		$statusEntity1->method('getId')->willReturn(1);
		$statusEntity1->method('getUserId')->willReturn(1);
		$statusEntity1->method('getWallId')->willReturn(1);

		$event1 = new StatusCreatedEvent([$statusEntity1]);

		$this->alertService->expects($this->never())
			->method('send');

		$this->listener->onStatusCreated($event1);

		// Scenario 2: User posts on different wall - send alert
		$listener2 = new StatusEventListener($this->alertService);

		$statusEntity2 = $this->createStub(StatusEntity::class);
		$statusEntity2->method('getId')->willReturn(2);
		$statusEntity2->method('getUserId')->willReturn(2);
		$statusEntity2->method('getWallId')->willReturn(3);

		$event2 = new StatusCreatedEvent([$statusEntity2]);

		$alertService2 = $this->createMock(AlertServiceInterface::class);
		$alertService2->expects($this->once())
			->method('send');

		$listener3 = new StatusEventListener($alertService2);
		$listener3->onStatusCreated($event2);
	}
}
