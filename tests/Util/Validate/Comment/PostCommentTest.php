<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Comment;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\StatusEntity;
use Breeze\Enums\PermissionsEnum;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Service\PermissionsServiceInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\Comment\PostComment;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class PostCommentTest extends TestCase
{
	private CommentRepositoryInterface | MockObject $commentRepository;

	private StatusRepositoryInterface | MockObject $statusRepository;

	private PermissionsServiceInterface | MockObject $permissionsService;

	private User | MockObject $validateUser;

	private Allow | MockObject $validateAllow;

	private PostComment $postComment;

	/**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		$this->commentRepository = $this->createMock(CommentRepositoryInterface::class);
		$this->statusRepository = $this->createMock(StatusRepositoryInterface::class);
		$this->permissionsService = $this->createMock(PermissionsServiceInterface::class);
		$this->validateUser = $this->createMock(User::class);
		$this->validateAllow = $this->createMock(Allow::class);

		$this->postComment = new PostComment(
			$this->createStub(Data::class),
			$this->validateUser,
			$this->validateAllow,
			$this->commentRepository,
			$this->statusRepository,
			$this->permissionsService
		);
	}

	public function testGetParams(): void
	{
		$this->assertEquals([
			'body' => '',
			'status_id' => 0,
			'user_id' => 0,
		], $this->postComment->getParams());
	}

	#[DataProvider('checkAllowProvider')]
	public function testCheckAllow(array $data, int $wallId, bool $canPost, bool $isExpectedException): void
	{
		$this->postComment->setData($data);

		// The wall owner is resolved from the parent status row, never from the
		// request payload.
		$this->statusRepository->expects($this->once())
			->method('getBasicInfoById')
			->with($data[CommentEntity::STATUS_ID])
			->willReturn(StatusEntity::from([
				StatusEntity::ID => $data[CommentEntity::STATUS_ID],
				StatusEntity::WALL_ID => $wallId,
				StatusEntity::USER_ID => 1,
			]));

		$this->permissionsService->expects($this->once())
			->method('canPost')
			->with(PermissionsEnum::TYPE_COMMENTS, $wallId)
			->willReturn($canPost);

		if ($isExpectedException) {
			// Flood control must not run once authorization fails.
			$this->validateAllow->expects($this->never())->method('floodControl');

			$this->expectException(NotAllowedException::class);
		} else {
			$this->validateAllow->expects($this->once())
				->method('floodControl')
				->with($data[CommentEntity::USER_ID]);
		}

		$this->postComment->checkAllow();
	}

	public static function checkAllowProvider(): array
	{
		return [
			'wall owner posting comment' => [
				'data' => [
					CommentEntity::STATUS_ID => 5,
					CommentEntity::USER_ID => 1,
					CommentEntity::BODY => 'Test',
				],
				'wallId' => 1,
				'canPost' => true,
				'isExpectedException' => false,
			],
			'non-owner with post comment permission' => [
				'data' => [
					CommentEntity::STATUS_ID => 5,
					CommentEntity::USER_ID => 1,
					CommentEntity::BODY => 'Test',
				],
				'wallId' => 2,
				'canPost' => true,
				'isExpectedException' => false,
			],
			'non-owner without post comment permission' => [
				'data' => [
					CommentEntity::STATUS_ID => 5,
					CommentEntity::USER_ID => 1,
					CommentEntity::BODY => 'Test',
				],
				'wallId' => 2,
				'canPost' => false,
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * Regression: commenting must always assert the poster is the session user,
	 * otherwise any member can attribute a comment to someone else.
	 */
	public function testCheckUserAssertsPosterIsSessionUser(): void
	{
		$this->postComment->setData([
			CommentEntity::STATUS_ID => 5,
			CommentEntity::USER_ID => 1,
			CommentEntity::BODY => 'Test',
		]);

		$this->validateUser->expects($this->once())
			->method('isSameUser')
			->with(1);

		$this->validateUser->expects($this->once())
			->method('areValidUsers')
			->with([1]);

		$this->postComment->checkUser();
	}

	public function testCheckUserRejectsSpoofedPoster(): void
	{
		$this->postComment->setData([
			CommentEntity::STATUS_ID => 5,
			CommentEntity::USER_ID => 999,
			CommentEntity::BODY => 'Test',
		]);

		$this->validateUser->expects($this->once())
			->method('isSameUser')
			->with(999)
			->willThrowException(new DataNotFoundException('invalid_users'));

		// Existence checks must never run for a spoofed poster.
		$this->validateUser->expects($this->never())->method('areValidUsers');

		$this->expectException(DataNotFoundException::class);

		$this->postComment->checkUser();
	}
}
