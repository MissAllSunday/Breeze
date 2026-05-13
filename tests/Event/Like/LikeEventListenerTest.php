<?php

declare(strict_types=1);

namespace Breeze\Event\Like;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\StatusEntity;
use Breeze\Enums\LikesEnum;
use Breeze\Repository\CommentRepository;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\StatusRepository;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Service\AlertServiceInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[AllowMockObjectsWithoutExpectations]
class LikeEventListenerTest extends TestCase
{
	private AlertServiceInterface|MockObject $alertService;

	private StatusRepositoryInterface|MockObject $statusRepository;

	private CommentRepositoryInterface|MockObject $commentRepository;

	private ContainerInterface|MockObject $container;

	private LikeEventListener $listener;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->alertService = $this->createMock(AlertServiceInterface::class);
		$this->statusRepository = $this->createMock(StatusRepositoryInterface::class);
		$this->commentRepository = $this->createMock(CommentRepositoryInterface::class);
		$this->container = $this->createMock(ContainerInterface::class);

		$this->listener = new LikeEventListener(
			$this->alertService,
			$this->container
		);
	}

	public function testOnLikeCreatedWithStatusType(): void
	{
		$likeEntity = $this->createMock(LikeEntity::class);
		$likeEntity->method('getContentId')->willReturn(123);
		$likeEntity->method('getContentType')->willReturn(LikesEnum::Status);
		$likeEntity->method('getIdMember')->willReturn(5);

		$statusEntity = $this->createMock(StatusEntity::class);
		$statusEntity->method('getUserId')->willReturn(10);
		$statusEntity->method('getId')->willReturn(123);

		$this->container->expects($this->once())
			->method('get')
			->with(StatusRepository::class)
			->willReturn($this->statusRepository);

		$this->statusRepository->expects($this->once())
			->method('getById')
			->with(123)
			->willReturn($statusEntity);

		$this->alertService->expects($this->once())
			->method('send');

		$event = new LikeCreatedEvent($likeEntity);
		$this->listener->onLikeCreated($event);
	}

	public function testOnLikeCreatedWithCommentType(): void
	{
		$likeEntity = $this->createMock(LikeEntity::class);
		$likeEntity->method('getContentId')->willReturn(456);
		$likeEntity->method('getContentType')->willReturn(LikesEnum::Comments);
		$likeEntity->method('getIdMember')->willReturn(7);

		$commentEntity = $this->createMock(CommentEntity::class);
		$commentEntity->method('getUserId')->willReturn(15);
		$commentEntity->method('getId')->willReturn(456);

		$this->container->expects($this->once())
			->method('get')
			->with(CommentRepository::class)
			->willReturn($this->commentRepository);

		$this->commentRepository->expects($this->once())
			->method('getById')
			->with(456)
			->willReturn($commentEntity);

		$this->alertService->expects($this->once())
			->method('send');

		$event = new LikeCreatedEvent($likeEntity);
		$this->listener->onLikeCreated($event);
	}

	public function testOnLikeCreatedSendsAlertToContentOwner(): void
	{
		$likeEntity = $this->createMock(LikeEntity::class);
		$likeEntity->method('getContentId')->willReturn(100);
		$likeEntity->method('getContentType')->willReturn(LikesEnum::Status);
		$likeEntity->method('getIdMember')->willReturn(2);

		$statusEntity = $this->createMock(StatusEntity::class);
		$statusEntity->method('getUserId')->willReturn(20);
		$statusEntity->method('getId')->willReturn(100);

		$this->container->method('get')
			->with(StatusRepository::class)
			->willReturn($this->statusRepository);

		$this->statusRepository->method('getById')->willReturn($statusEntity);

		$this->alertService->expects($this->once())
			->method('send')
			->with($this->callback(function ($alert) {
				return $alert->getIdMember() === 20;
			}));

		$event = new LikeCreatedEvent($likeEntity);
		$this->listener->onLikeCreated($event);
	}

	public function testOnLikeCreatedSkipsAlertWhenLikingOwnContent(): void
	{
		$likeEntity = $this->createMock(LikeEntity::class);
		$likeEntity->method('getContentId')->willReturn(100);
		$likeEntity->method('getContentType')->willReturn(LikesEnum::Status);
		$likeEntity->method('getIdMember')->willReturn(10);

		$statusEntity = $this->createMock(StatusEntity::class);
		$statusEntity->method('getUserId')->willReturn(10);
		$statusEntity->method('getId')->willReturn(100);

		$this->container->method('get')
			->with(StatusRepository::class)
			->willReturn($this->statusRepository);

		$this->statusRepository->method('getById')->willReturn($statusEntity);

		$this->alertService->expects($this->never())
			->method('send');

		$event = new LikeCreatedEvent($likeEntity);
		$this->listener->onLikeCreated($event);
	}

	public function testGetContentReturnsStatusEntity(): void
	{
		$likeEntity = $this->createMock(LikeEntity::class);
		$likeEntity->method('getContentId')->willReturn(200);
		$likeEntity->method('getContentType')->willReturn(LikesEnum::Status);

		$statusEntity = $this->createMock(StatusEntity::class);

		$this->container->expects($this->once())
			->method('get')
			->with(StatusRepository::class)
			->willReturn($this->statusRepository);

		$this->statusRepository->expects($this->once())
			->method('getById')
			->with(200)
			->willReturn($statusEntity);

		$reflection = new \ReflectionClass($this->listener);
		$method = $reflection->getMethod('getContent');
		$method->setAccessible(true);

		$result = $method->invoke($this->listener, $likeEntity);

		$this->assertSame($statusEntity, $result);
	}

	public function testGetContentReturnsCommentEntity(): void
	{
		$likeEntity = $this->createMock(LikeEntity::class);
		$likeEntity->method('getContentId')->willReturn(300);
		$likeEntity->method('getContentType')->willReturn(LikesEnum::Comments);

		$commentEntity = $this->createMock(CommentEntity::class);

		$this->container->expects($this->once())
			->method('get')
			->with(CommentRepository::class)
			->willReturn($this->commentRepository);

		$this->commentRepository->expects($this->once())
			->method('getById')
			->with(300)
			->willReturn($commentEntity);

		$reflection = new \ReflectionClass($this->listener);
		$method = $reflection->getMethod('getContent');
		$method->setAccessible(true);

		$result = $method->invoke($this->listener, $likeEntity);

		$this->assertSame($commentEntity, $result);
	}
}
