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
use Breeze\Util\Validate\Validations\Comment\DeleteComment;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class DeleteCommentTest extends TestCase
{
	private CommentRepositoryInterface | MockObject $commentRepository;

	private StatusRepositoryInterface | MockObject $statusRepository;

	private PermissionsServiceInterface | MockObject $permissionsService;

	private User | MockObject $validateUser;

	private DeleteComment $deleteComment;

	/**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		$this->commentRepository = $this->createMock(CommentRepositoryInterface::class);
		$this->statusRepository = $this->createMock(StatusRepositoryInterface::class);
		$this->permissionsService = $this->createMock(PermissionsServiceInterface::class);
		$this->validateUser = $this->createMock(User::class);

		$this->deleteComment = new DeleteComment(
			$this->createStub(Data::class),
			$this->validateUser,
			$this->createStub(Allow::class),
			$this->commentRepository,
			$this->statusRepository,
			$this->permissionsService
		);
	}

	public function testGetParams(): void
	{
		// user_id is intentionally absent: the author is always read from the
		// stored row, never from the request payload.
		$this->assertEquals([CommentEntity::ID => 0], $this->deleteComment->getParams());
	}

	public function testSuccessKeyString(): void
	{
		$this->assertEquals('deleted_comment', $this->deleteComment->successKeyString());
	}

	#[DataProvider('checkAllowProvider')]
	public function testCheckAllow(
		int $storedAuthorId,
		int $storedWallId,
		bool $canDelete,
		bool $isExpectedException
	): void {
		$this->deleteComment->setData([CommentEntity::ID => 5]);

		$this->commentRepository->method('getById')->willReturn(CommentEntity::from([
			CommentEntity::ID => 5,
			CommentEntity::STATUS_ID => 10,
			CommentEntity::USER_ID => $storedAuthorId,
		]));

		$this->statusRepository->method('getBasicInfoById')->willReturn(StatusEntity::from([
			StatusEntity::ID => 10,
			StatusEntity::WALL_ID => $storedWallId,
		]));

		$this->permissionsService->expects($this->once())
			->method('canDelete')
			->with(PermissionsEnum::TYPE_COMMENTS, $storedAuthorId, $storedWallId)
			->willReturn($canDelete);

		if ($isExpectedException) {
			$this->expectException(NotAllowedException::class);
		}

		$this->deleteComment->checkAllow();
	}

	public static function checkAllowProvider(): array
	{
		return [
			'author deletes own comment' => [
				'storedAuthorId' => 666,
				'storedWallId' => 1,
				'canDelete' => true,
				'isExpectedException' => false,
			],
			'wall owner deletes foreign comment on own wall' => [
				'storedAuthorId' => 2,
				'storedWallId' => 666,
				'canDelete' => true,
				'isExpectedException' => false,
			],
			'moderator deletes any comment' => [
				'storedAuthorId' => 2,
				'storedWallId' => 1,
				'canDelete' => true,
				'isExpectedException' => false,
			],
			'unauthorized member is denied' => [
				'storedAuthorId' => 2,
				'storedWallId' => 1,
				'canDelete' => false,
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * Regression: a spoofed user_id in the payload must not influence the
	 * authorization decision. The stored author (2) is used, not the
	 * client-supplied 666.
	 */
	public function testCheckAllowIgnoresClientSuppliedUserId(): void
	{
		$this->deleteComment->setData([
			CommentEntity::ID => 5,
			CommentEntity::USER_ID => 666,
		]);

		$this->commentRepository->method('getById')->willReturn(CommentEntity::from([
			CommentEntity::ID => 5,
			CommentEntity::STATUS_ID => 10,
			CommentEntity::USER_ID => 2,
		]));

		$this->statusRepository->method('getBasicInfoById')->willReturn(StatusEntity::from([
			StatusEntity::ID => 10,
			StatusEntity::WALL_ID => 1,
		]));

		$this->permissionsService->expects($this->once())
			->method('canDelete')
			->with(PermissionsEnum::TYPE_COMMENTS, 2, 1)
			->willReturn(false);

		$this->expectException(NotAllowedException::class);

		$this->deleteComment->checkAllow();
	}

	#[DataProvider('checkUserProvider')]
	public function testCheckUser(int $storedAuthorId, bool $isExpectedException): void
	{
		$this->deleteComment->setData([CommentEntity::ID => 5]);

		$this->commentRepository->method('getById')->willReturn(CommentEntity::from([
			CommentEntity::ID => 5,
			CommentEntity::STATUS_ID => 10,
			CommentEntity::USER_ID => $storedAuthorId,
		]));

		$this->validateUser->expects($this->once())
			->method('areValidUsers')
			->with([$storedAuthorId]);

		if ($isExpectedException) {
			$this->validateUser->method('areValidUsers')
				->willThrowException(new DataNotFoundException());

			$this->expectException(DataNotFoundException::class);
		}

		$this->deleteComment->checkUser();
	}

	public static function checkUserProvider(): array
	{
		return [
			'validUsers' => [
				'storedAuthorId' => 666,
				'isExpectedException' => false,
			],
			'invalidUsers' => [
				'storedAuthorId' => 2,
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * checkUser() must validate the stored author, not the payload one.
	 */
	public function testCheckUserIgnoresClientSuppliedUserId(): void
	{
		$this->deleteComment->setData([
			CommentEntity::ID => 5,
			CommentEntity::USER_ID => 666,
		]);

		$this->commentRepository->method('getById')->willReturn(CommentEntity::from([
			CommentEntity::ID => 5,
			CommentEntity::STATUS_ID => 10,
			CommentEntity::USER_ID => 2,
		]));

		$this->validateUser->expects($this->once())
			->method('areValidUsers')
			->with([2]);

		$this->deleteComment->checkUser();
	}

	public function testIsValidRunsDataAllowAndUserChecks(): void
	{
		$this->deleteComment->setData([CommentEntity::ID => 5]);

		$this->commentRepository->method('getById')->willReturn(CommentEntity::from([
			CommentEntity::ID => 5,
			CommentEntity::STATUS_ID => 10,
			CommentEntity::USER_ID => 2,
		]));

		$this->statusRepository->method('getBasicInfoById')->willReturn(StatusEntity::from([
			StatusEntity::ID => 10,
			StatusEntity::WALL_ID => 1,
		]));

		$this->permissionsService->method('canDelete')->willReturn(true);

		$this->validateUser->expects($this->once())
			->method('areValidUsers')
			->with([2]);

		$this->deleteComment->isValid();

		$this->assertSame([CommentEntity::ID => 5], $this->deleteComment->data);
	}

	public function testIsValidStopsOnNotAllowed(): void
	{
		$this->deleteComment->setData([CommentEntity::ID => 5]);

		$this->commentRepository->method('getById')->willReturn(CommentEntity::from([
			CommentEntity::ID => 5,
			CommentEntity::STATUS_ID => 10,
			CommentEntity::USER_ID => 2,
		]));

		$this->statusRepository->method('getBasicInfoById')->willReturn(StatusEntity::from([
			StatusEntity::ID => 10,
			StatusEntity::WALL_ID => 1,
		]));

		$this->permissionsService->method('canDelete')->willReturn(false);

		// checkUser() must never run once authorization fails.
		$this->validateUser->expects($this->never())->method('areValidUsers');

		$this->expectException(NotAllowedException::class);

		$this->deleteComment->isValid();
	}
}
