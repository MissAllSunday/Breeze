<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\StatusEntity;
use Breeze\Enums\PermissionsEnum;
use Breeze\Event\Comment\CommentCreatedEvent;
use Breeze\Event\EventServiceProvider;
use Breeze\Fixtures\CommentFixtures;
use Breeze\Fixtures\StatusFixtures;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\InvalidCommentException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use League\Event\EventDispatcher;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class CommentServiceTest extends TestCase
{
	private const int SESSION_USER_ID = 666;

	private MockObject|CommentRepositoryInterface $commentRepository;

	private MockObject|StatusRepositoryInterface $statusRepository;

	private MockObject|EventServiceProvider $eventServiceProvider;

	private MockObject|EventDispatcher $eventDispatcher;

	private MockObject|PermissionsServiceInterface $permissionsService;

	private MockObject|WallVisibilityServiceInterface $wallVisibilityService;

	private CommentService $commentService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$GLOBALS['user_info'] = ['id' => self::SESSION_USER_ID, 'is_guest' => false];

		$this->commentRepository = $this->createMock(CommentRepositoryInterface::class);
		$this->statusRepository = $this->createMock(StatusRepositoryInterface::class);
		$this->eventServiceProvider = $this->createMock(EventServiceProvider::class);
		$this->eventDispatcher = $this->createMock(EventDispatcher::class);
		$this->permissionsService = $this->createMock(PermissionsServiceInterface::class);
		$this->wallVisibilityService = $this->createMock(WallVisibilityServiceInterface::class);

		// Default: the wall is accessible and the session user may comment.
		$this->wallVisibilityService->method('canAccessWall')->willReturn(true);
		$this->permissionsService->method('canPost')->willReturn(true);

		$this->commentService = new CommentService(
			$this->commentRepository,
			$this->statusRepository,
			$this->eventServiceProvider,
			$this->permissionsService,
			$this->wallVisibilityService
		);
	}

	private function stubParentStatus(): void
	{
		$this->statusRepository->method('getBasicInfoById')
			->willReturn(StatusEntity::from(StatusFixtures::basic()));
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
					   $entity->getUserId() === self::SESSION_USER_ID &&
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
	 * @throws NotAllowedException
	 */
	public function testDeleteById(): void
	{
		$commentId = 123;

		$this->commentRepository->method('getById')
			->willReturn(CommentEntity::from(CommentFixtures::withCustomData([CommentEntity::ID => $commentId])));
		$this->statusRepository->method('getBasicInfoById')
			->willReturn(StatusEntity::from(StatusFixtures::basic()));
		$this->permissionsService->expects($this->once())
			->method('canDelete')
			->with(PermissionsEnum::TYPE_COMMENTS, 2, 1)
			->willReturn(true);
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

		$this->commentRepository->method('getById')
			->willThrowException(new DataNotFoundException('error_no_comment'));
		$this->commentRepository->expects($this->never())->method('deleteById');

		$this->commentService->deleteById($commentId);
	}

	/**
	 * The service is the last gate: a caller that skips the validator must
	 * still be denied, and the ids it passes must never be trusted.
	 */
	public function testDeleteByIdThrowsWhenNotAllowed(): void
	{
		$this->commentRepository->method('getById')
			->willReturn(CommentEntity::from(CommentFixtures::withCustomData([CommentEntity::USER_ID => 2])));
		$this->statusRepository->method('getBasicInfoById')
			->willReturn(StatusEntity::from(StatusFixtures::withCustomData([StatusEntity::WALL_ID => 2])));
		$this->permissionsService->expects($this->once())
			->method('canDelete')
			->with(PermissionsEnum::TYPE_COMMENTS, 2, 2)
			->willReturn(false);
		$this->commentRepository->expects($this->never())->method('deleteById');

		$this->expectException(NotAllowedException::class);
		$this->expectExceptionMessage(PermissionsEnum::DELETE_COMMENTS);

		$this->commentService->deleteById(123);
	}

	/**
	 * Attribution is taken from the session, so a spoofed `user_id` in the
	 * payload cannot create content owned by someone else.
	 */
	public function testSaveForcesSessionUserAsAuthor(): void
	{
		$commentData = CommentFixtures::forInsertion();
		$commentData[CommentEntity::USER_ID] = 2;

		$this->commentRepository->expects($this->once())
			->method('insert')
			->with($this->callback(static fn ($e) => $e->getUserId() === self::SESSION_USER_ID))
			->willReturn([]);

		$this->commentService->save($commentData);
	}

	public function testSaveThrowsWhenUserCannotPost(): void
	{
		$permissionsService = $this->createStub(PermissionsServiceInterface::class);
		$permissionsService->method('canPost')->willReturn(false);

		$commentService = new CommentService(
			$this->commentRepository,
			$this->statusRepository,
			$this->eventServiceProvider,
			$permissionsService,
			$this->wallVisibilityService
		);

		$this->stubParentStatus();
		$this->commentRepository->expects($this->never())->method('insert');

		$this->expectException(NotAllowedException::class);
		$this->expectExceptionMessage(PermissionsEnum::POST_COMMENTS);

		$commentService->save(CommentFixtures::forInsertion());
	}

	public function testSaveThrowsWhenWallIsNotAccessible(): void
	{
		$wallVisibilityService = $this->createStub(WallVisibilityServiceInterface::class);
		$wallVisibilityService->method('canAccessWall')->willReturn(false);

		$commentService = new CommentService(
			$this->commentRepository,
			$this->statusRepository,
			$this->eventServiceProvider,
			$this->permissionsService,
			$wallVisibilityService
		);

		$this->stubParentStatus();
		$this->commentRepository->expects($this->never())->method('insert');

		$this->expectException(NotAllowedException::class);
		$this->expectExceptionMessage(PermissionsEnum::POST_COMMENTS);

		$commentService->save(CommentFixtures::forInsertion());
	}

	/**
	 * A missing parent must fail before any insert instead of leaving an
	 * orphan comment behind.
	 */
	public function testSaveThrowsBeforeInsertWhenParentStatusIsMissing(): void
	{
		$statusRepository = $this->createStub(StatusRepositoryInterface::class);
		$statusRepository->method('getBasicInfoById')
			->willThrowException(new DataNotFoundException('error_no_status'));

		$commentService = new CommentService(
			$this->commentRepository,
			$statusRepository,
			$this->eventServiceProvider,
			$this->permissionsService,
			$this->wallVisibilityService
		);

		$this->commentRepository->expects($this->never())->method('insert');

		$this->expectException(DataNotFoundException::class);

		$commentService->save(CommentFixtures::forInsertion());
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
			$this->permissionsService,
			$this->wallVisibilityService,
			$mentionService
		);

		$this->stubParentStatus();
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
			$this->permissionsService,
			$this->wallVisibilityService,
			$mentionService
		);

		$this->stubParentStatus();
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
