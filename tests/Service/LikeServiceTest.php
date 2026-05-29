<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\LikeEntity;
use Breeze\Entity\LikeInfoEntity;
use Breeze\Enums\LikesEnum;
use Breeze\Event\EventServiceProvider;
use Breeze\Event\Like\LikeCreatedEvent;
use Breeze\Repository\InvalidLikeException;
use Breeze\Repository\LikeRepositoryInterface;
use Breeze\Util\Validate\InvalidDataException;
use League\Event\EventDispatcher;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class LikeServiceTest extends TestCase
{
	private MockObject|LikeRepositoryInterface $likeRepository;

	private MockObject|EventServiceProvider $eventServiceProvider;

	private MockObject|EventDispatcher $eventDispatcher;

	private LikeService $likeService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->likeRepository = $this->createMock(LikeRepositoryInterface::class);
		$this->eventServiceProvider = $this->createMock(EventServiceProvider::class);
		$this->eventDispatcher = $this->createMock(EventDispatcher::class);

		$this->likeService = new LikeService(
			$this->likeRepository,
			$this->eventServiceProvider
		);
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidLikeException
	 * @throws Exception
	 */
	public function testLikeContentDispatchesEventWhenNewLike(): void
	{
		$type = LikesEnum::Status;
		$contentId = 123;
		$userId = 5;

		$likeInfo = $this->createMock(LikeInfoEntity::class);
		$likeInfo->method('isAlreadyLiked')->willReturn(true);

		$this->likeRepository->expects($this->once())
			->method('likeContent')
			->with($this->callback(function ($entity) use ($type, $contentId, $userId) {
				return $entity instanceof LikeEntity &&
					   $entity->getContentId() === $contentId &&
					   $entity->getIdMember() === $userId &&
					   $entity->getContentType() === $type;
			}))
			->willReturn($likeInfo);

		$this->eventServiceProvider->expects($this->once())
			->method('getDispatcher')
			->willReturn($this->eventDispatcher);

		$this->eventDispatcher->expects($this->once())
			->method('dispatch')
			->with($this->callback(function ($event) {
				return $event instanceof LikeCreatedEvent;
			}));

		$result = $this->likeService->likeContent($type, $contentId, $userId);

		$this->assertSame($likeInfo, $result);
	}

	public function testCountOrphans(): void
	{
		$expectedCount = 5;

		$this->likeRepository->expects($this->once())
			->method('countOrphans')
			->willReturn($expectedCount);

		$result = $this->likeService->countOrphans();

		$this->assertEquals($expectedCount, $result);
	}

	public function testDeleteOrphans(): void
	{
		$this->likeRepository->expects($this->once())
			->method('deleteOrphans');

		$this->likeService->deleteOrphans();
	}
}
