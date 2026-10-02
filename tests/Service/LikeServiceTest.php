<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\LikeInfoEntity;
use Breeze\Entity\StatusEntity;
use Breeze\Enums\LikesEnum;
use Breeze\Event\EventServiceProvider;
use Breeze\Event\Like\LikeCreatedEvent;
use Breeze\Fixtures\CommentFixtures;
use Breeze\Fixtures\StatusFixtures;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\InvalidLikeException;
use Breeze\Repository\LikeRepositoryInterface;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\InvalidDataException;
use League\Event\EventDispatcher;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class LikeServiceTest extends TestCase
{
	private const int SESSION_USER_ID = 5;

	private MockObject|LikeRepositoryInterface $likeRepository;

	private MockObject|EventServiceProvider $eventServiceProvider;

	private MockObject|EventDispatcher $eventDispatcher;

	private MockObject|StatusRepositoryInterface $statusRepository;

	private MockObject|CommentRepositoryInterface $commentRepository;

	private MockObject|WallVisibilityServiceInterface $wallVisibilityService;

	private LikeService $likeService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$GLOBALS['user_info'] = ['id' => self::SESSION_USER_ID, 'is_guest' => false];

		$this->likeRepository = $this->createMock(LikeRepositoryInterface::class);
		$this->eventServiceProvider = $this->createMock(EventServiceProvider::class);
		$this->eventDispatcher = $this->createMock(EventDispatcher::class);
		$this->statusRepository = $this->createMock(StatusRepositoryInterface::class);
		$this->commentRepository = $this->createMock(CommentRepositoryInterface::class);
		$this->wallVisibilityService = $this->createMock(WallVisibilityServiceInterface::class);

		$this->likeService = $this->buildService($this->wallVisibilityService);
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

		$this->statusRepository->method('getBasicInfoById')
			->with($contentId)
			->willReturn(StatusEntity::from(StatusFixtures::withCustomData([StatusEntity::WALL_ID => 9])));
		$this->wallVisibilityService->expects($this->once())
			->method('canAccessWall')
			->with(9, self::SESSION_USER_ID)
			->willReturn(true);

		$likeInfo = $this->createMock(LikeInfoEntity::class);
		$likeInfo->method('isAlreadyLiked')->willReturn(true);

		$this->likeRepository->expects($this->once())
			->method('likeContent')
			->with($this->callback(function ($entity) use ($type, $contentId) {
				return $entity instanceof LikeEntity &&
					   $entity->getContentId() === $contentId &&
					   $entity->getIdMember() === self::SESSION_USER_ID &&
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

		$result = $this->likeService->likeContent($type, $contentId);

		$this->assertSame($likeInfo, $result);
	}

	/**
	 * A comment like is authorized against the wall of the comment's parent
	 * status, resolved from persisted rows.
	 */
	public function testLikeCommentResolvesWallFromParentStatus(): void
	{
		$this->commentRepository->method('getById')
			->with(456)
			->willReturn(CommentEntity::from(CommentFixtures::withCustomData([CommentEntity::STATUS_ID => 77])));
		$this->statusRepository->expects($this->once())
			->method('getBasicInfoById')
			->with(77)
			->willReturn(StatusEntity::from(StatusFixtures::withCustomData([StatusEntity::WALL_ID => 12])));
		$this->wallVisibilityService->expects($this->once())
			->method('canAccessWall')
			->with(12, self::SESSION_USER_ID)
			->willReturn(true);

		$this->likeRepository->expects($this->once())
			->method('likeContent')
			->willReturn(null);
		$this->eventServiceProvider->method('getDispatcher')->willReturn($this->eventDispatcher);

		$this->likeService->likeContent(LikesEnum::Comments, 456);
	}

	public function testLikeContentThrowsWhenWallIsNotAccessible(): void
	{
		$this->statusRepository->method('getBasicInfoById')
			->willReturn(StatusEntity::from(StatusFixtures::basic()));
		$this->wallVisibilityService->method('canAccessWall')->willReturn(false);

		$this->likeRepository->expects($this->never())->method('likeContent');
		$this->eventServiceProvider->expects($this->never())->method('getDispatcher');

		$this->expectException(DataNotFoundException::class);
		$this->expectExceptionMessage('error_no_data');

		$this->likeService->likeContent(LikesEnum::Status, 123);
	}

	public function testLikeContentThrowsWhenContentDoesNotExist(): void
	{
		$this->statusRepository->method('getBasicInfoById')
			->willThrowException(new DataNotFoundException('error_no_status'));

		$this->likeRepository->expects($this->never())->method('likeContent');

		$this->expectException(DataNotFoundException::class);

		$this->likeService->likeContent(LikesEnum::Status, 999);
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

	private function buildService(WallVisibilityServiceInterface $wallVisibilityService): LikeService
	{
		return new LikeService(
			$this->likeRepository,
			$this->eventServiceProvider,
			$this->statusRepository,
			$this->commentRepository,
			$wallVisibilityService
		);
	}
}
