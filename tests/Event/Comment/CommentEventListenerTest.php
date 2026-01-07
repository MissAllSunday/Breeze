<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use Breeze\Entity\AlertEntity;
use Breeze\Entity\CommentEntity;
use Breeze\Entity\StatusEntity;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Service\AlertServiceInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class CommentEventListenerTest extends TestCase
{
	private AlertServiceInterface|MockObject $alertService;

	private CommentEventListener $listener;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->alertService = $this->createMock(AlertServiceInterface::class);
		$statusRepository = $this->createMock(StatusRepositoryInterface::class);
		$this->listener = new CommentEventListener($this->alertService, $statusRepository);
	}

	/**
	 * @throws Exception
	 */
	public function testOnCommentCreatedWhenUserPostsOnOwnWall(): void
	{
		$commentEntity = $this->createMock(CommentEntity::class);
		$commentEntity->method('getStatusId')->willReturn(1);
		$commentEntity->method('getId')->willReturn(10);
		$commentEntity->method('getUserId')->willReturn(5);

		$statusEntity = $this->createStub(StatusEntity::class);
		$statusEntity->method('getUserId')->willReturn(5);
		$statusEntity->method('getWallId')->willReturn(5);

		$event = new CommentCreatedEvent($commentEntity, $statusEntity);

		// Should not send any alerts when user posts on their own wall
		$this->alertService->expects($this->never())
			->method('send');

		$this->listener->onCommentCreated($event);
	}

	/**
	 * @throws Exception
	 */
	public function testOnCommentCreatedSendsAlertToStatusOwner(): void
	{
		$commentEntity = $this->createMock(CommentEntity::class);
		$commentEntity->method('getStatusId')->willReturn(1);
		$commentEntity->method('getId')->willReturn(10);
		$commentEntity->method('getUserId')->willReturn(2);

		$statusEntity = $this->createStub(StatusEntity::class);
		$statusEntity->method('getUserId')->willReturn(3);
		$statusEntity->method('getWallId')->willReturn(2);

		$event = new CommentCreatedEvent($commentEntity, $statusEntity);

		$this->alertService->expects($this->once())
			->method('send')
			->with($this->callback(function ($alert) {
				return $alert instanceof AlertEntity &&
					$alert->getIdMember() === 3;
			}));

		$this->listener->onCommentCreated($event);
	}

	/**
	 * @throws Exception
	 */
	public function testOnCommentCreatedSendsAlertToWallOwner(): void
	{
		$commentEntity = $this->createMock(CommentEntity::class);
		$commentEntity->method('getStatusId')->willReturn(1);
		$commentEntity->method('getId')->willReturn(10);
		$commentEntity->method('getUserId')->willReturn(2);

		$statusEntity = $this->createStub(StatusEntity::class);
		$statusEntity->method('getUserId')->willReturn(2);
		$statusEntity->method('getWallId')->willReturn(5);

		$event = new CommentCreatedEvent($commentEntity, $statusEntity);

		$this->alertService->expects($this->once())
			->method('send')
			->with($this->callback(function ($alert) {
				return $alert instanceof AlertEntity &&
					$alert->getIdMember() === 5;
			}));

		$this->listener->onCommentCreated($event);
	}

	/**
	 * @throws Exception
	 */
	public function testOnCommentCreatedSendsAlertsToStatusOwnerAndWallOwner(): void
	{
		$commentEntity = $this->createMock(CommentEntity::class);
		$commentEntity->method('getStatusId')->willReturn(1);
		$commentEntity->method('getId')->willReturn(10);
		$commentEntity->method('getUserId')->willReturn(2);

		$statusEntity = $this->createStub(StatusEntity::class);
		$statusEntity->method('getUserId')->willReturn(3);
		$statusEntity->method('getWallId')->willReturn(4);

		$event = new CommentCreatedEvent($commentEntity, $statusEntity);

		$this->alertService->expects($this->exactly(2))
			->method('send');

		$this->listener->onCommentCreated($event);
	}

	public function testOnCommentDeletedSendsAlertToWallOwner(): void
	{
		$event = new CommentDeletedEvent(10, 2, 1, 2, 4);

		$this->alertService->expects($this->once())
			->method('send')
			->with($this->callback(function ($alert) {
				return $alert instanceof AlertEntity &&
					$alert->getIdMember() === 2;
			}));

		$this->listener->onCommentDeleted($event);
	}

	public function testOnCommentDeletedSendsAlertToStatusOwner(): void
	{
		$event = new CommentDeletedEvent(10, 2, 1, 3, 2);

		$this->alertService->expects($this->once())
			->method('send')
			->with($this->callback(function ($alert) {
				return $alert instanceof AlertEntity &&
					$alert->getIdMember() === 3;
			}));

		$this->listener->onCommentDeleted($event);
	}

	public function testOnCommentDeletedDoesNotSendAlertWhenUserIsOwner(): void
	{
		$event = new CommentDeletedEvent(10, 2, 1, 2, 2);

		$this->alertService->expects($this->never())
			->method('send');

		$this->listener->onCommentDeleted($event);
	}

	/**
	 * @throws Exception
	 */
	public function testOnCommentCreatedWithDifferentOwners(): void
	{
		$commentEntity = $this->createMock(CommentEntity::class);
		$commentEntity->method('getStatusId')->willReturn(100);
		$commentEntity->method('getId')->willReturn(200);
		$commentEntity->method('getUserId')->willReturn(2);

		$statusEntity = $this->createStub(StatusEntity::class);
		$statusEntity->method('getUserId')->willReturn(4);
		$statusEntity->method('getWallId')->willReturn(3);

		$event = new CommentCreatedEvent($commentEntity, $statusEntity);

		$this->alertService->expects($this->exactly(2))
			->method('send');

		$this->listener->onCommentCreated($event);
	}
}
