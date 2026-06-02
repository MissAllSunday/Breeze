<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\StatusEntity;
use Breeze\Event\Comment\CommentCreatedEvent;
use Breeze\Event\EventServiceProvider;
use Breeze\Fixtures\CommentFixtures;
use Breeze\Fixtures\StatusFixtures;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\InvalidCommentException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use League\Event\EventDispatcher;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class CommentServiceTest extends TestCase
{
	private MockObject|CommentRepositoryInterface $commentRepository;

	private MockObject|StatusRepositoryInterface $statusRepository;

	private MockObject|EventServiceProvider $eventServiceProvider;

	private MockObject|EventDispatcher $eventDispatcher;

	private CommentService $commentService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->commentRepository = $this->createMock(CommentRepositoryInterface::class);
		$this->statusRepository = $this->createMock(StatusRepositoryInterface::class);
		$this->eventServiceProvider = $this->createMock(EventServiceProvider::class);
		$this->eventDispatcher = $this->createMock(EventDispatcher::class);

		$this->commentService = new CommentService(
			$this->commentRepository,
			$this->statusRepository,
			$this->eventServiceProvider
		);
	}

	/**
	 * @throws InvalidCommentException
	 * @throws DataNotFoundException
	 */
	public function testSaveDispatchesEvent(): void
	{
		$commentData = CommentFixtures::forInsertion();
		$commentEntity = CommentEntity::from($commentData);
		$savedCommentEntity = CommentEntity::from(array_merge($commentData, [CommentEntity::ID => 123]));
		$commentEntities = [$savedCommentEntity];

		$statusData = StatusFixtures::basic();
		$statusEntity = StatusEntity::from($statusData);

		$this->commentRepository->expects($this->once())
			->method('insert')
			->with($this->callback(function ($entity) use ($commentEntity) {
				return $entity->getStatusId() === $commentEntity->getStatusId() &&
					   $entity->getUserId() === $commentEntity->getUserId() &&
					   $entity->getBody() === $commentEntity->getBody();
			}))
			->willReturn($commentEntities);

		$this->statusRepository->expects($this->once())
			->method('getBasicInfoById')
			->with($commentData[CommentEntity::STATUS_ID])
			->willReturn($statusEntity);

		$this->eventServiceProvider->expects($this->once())
			->method('getDispatcher')
			->willReturn($this->eventDispatcher);

		$this->eventDispatcher->expects($this->once())
			->method('dispatch')
			->with($this->callback(function ($event) use ($savedCommentEntity, $statusEntity) {
				return $event instanceof CommentCreatedEvent &&
					   $event->getCommentEntity() === $savedCommentEntity &&
					   $event->getStatusEntity() === $statusEntity;
			}));

		$result = $this->commentService->save($commentData);

		$this->assertEquals($commentEntities, $result);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function testDeleteById(): void
	{
		$commentId = 123;

		$this->commentRepository->expects($this->once())
			->method('deleteById')
			->with($commentId)
			->willReturn(true);

		$result = $this->commentService->deleteById($commentId);

		$this->assertTrue($result);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function testDeleteByIdThrowsException(): void
	{
		$this->expectException(DataNotFoundException::class);

		$commentId = 999;

		$this->commentRepository->expects($this->once())
			->method('deleteById')
			->with($commentId)
			->willThrowException(new DataNotFoundException('error_no_comment'));

		$this->commentService->deleteById($commentId);
	}

	public function testCountOrphans(): void
	{
		$expectedCount = 5;

		$this->commentRepository->expects($this->once())
			->method('countOrphans')
			->willReturn($expectedCount);

		$result = $this->commentService->countOrphans();

		$this->assertEquals($expectedCount, $result);
	}

	public function testDeleteOrphans(): void
	{
		$this->commentRepository->expects($this->once())
			->method('deleteOrphans');

		$this->commentService->deleteOrphans();
	}

	public function testRecountLikes(): void
	{
		$this->commentRepository->expects($this->once())
			->method('recountLikes');

		$this->commentService->recountLikes();
	}

	public function testSaveCallsProcessBodyWithMentionIdsAndRewritesBody(): void
	{
		$mentionService = $this->createMock(MentionServiceInterface::class);
		$commentService = new CommentService(
			$this->commentRepository,
			$this->statusRepository,
			$this->eventServiceProvider,
			$mentionService
		);

		$memberId       = 42;
		$originalBody   = 'Hey @Alice!';
		$rewrittenBody  = 'Hey [member=42]Alice[/member]!';
		$commentData    = CommentFixtures::forInsertion();
		$commentData[CommentEntity::BODY] = $originalBody;
		$commentData['mention_ids']       = [$memberId];

		$mentionService->expects($this->once())
			->method('isEnabled')
			->willReturn(true);

		$mentionService->expects($this->once())
			->method('processBody')
			->with($originalBody, [$memberId])
			->willReturn(['body' => $rewrittenBody, 'members' => []]);

		// insert receives the rewritten body
		$this->commentRepository->expects($this->once())
			->method('insert')
			->with($this->callback(static fn ($e) => $e->getBody() === $rewrittenBody))
			->willReturn([]);

		$commentService->save($commentData);
	}

	public function testSaveSkipsMentionProcessingWhenMentionServiceIsDisabled(): void
	{
		$mentionService = $this->createMock(MentionServiceInterface::class);
		$commentService = new CommentService(
			$this->commentRepository,
			$this->statusRepository,
			$this->eventServiceProvider,
			$mentionService
		);

		$commentData                = CommentFixtures::forInsertion();
		$commentData['mention_ids'] = [42];

		$mentionService->expects($this->once())
			->method('isEnabled')
			->willReturn(false);

		$mentionService->expects($this->never())
			->method('processBody');

		$this->commentRepository->method('insert')->willReturn([]);

		$commentService->save($commentData);
	}
}
