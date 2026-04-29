<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Comment;

use Breeze\Entity\CommentEntity;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\Comment\DeleteComment;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class DeleteCommentTest extends TestCase
{
	private CommentRepositoryInterface | MockObject $commentRepository;

	private Allow | MockObject $validateAllow;

	private User | MockObject $validateUser;

	private StatusRepositoryInterface | MockObject $statusRepository;

	private DeleteComment $deleteComment;

	/**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		$this->commentRepository = $this->createMock(CommentRepositoryInterface::class);
		$this->validateAllow = $this->createMock(Allow::class);
		$this->validateUser = $this->createMock(User::class);
		$validateData = $this->createStub(Data::class);
		$this->statusRepository = $this->createMock(StatusRepositoryInterface::class);

		$this->deleteComment = new DeleteComment(
			$validateData,
			$this->validateUser,
			$this->validateAllow,
			$this->commentRepository,
			$this->statusRepository
		);
	}

	public function testGetParams(): void
	{
		$this->assertEquals([
			'id' => 0,
			'user_id' => 0,
		], $this->deleteComment->getParams());
	}

	public function testSuccessKeyString(): void
	{
		$this->assertEquals('deleted_comment', $this->deleteComment->successKeyString());
	}

	public function testCheckAllow(): void
	{
		$this->deleteComment->setData([
			CommentEntity::ID => 0,
			CommentEntity::USER_ID => 2,
		]);
		$this->commentRepository->expects($this->once())
			->method('getCurrentUserInfo')
			->willReturn(['id' => 2]);
		$this->validateAllow->expects($this->once())
			->method('permissions')
			->willThrowException(new NotAllowedException());

		$this->expectException(NotAllowedException::class);

		$this->deleteComment->checkAllow();
	}

	public function testCheckUser(): void
	{
		$this->deleteComment->setData([
			CommentEntity::ID => 0,
			CommentEntity::USER_ID => 2,
		]);

		$this->validateUser->expects($this->once())
			->method('areValidUsers')
			->willThrowException(new DataNotFoundException());

		$this->expectException(DataNotFoundException::class);

		$this->deleteComment->checkUser();
	}

	public function testCheckAllowAsProfileOwner(): void
	{
		$this->deleteComment->setData([
			CommentEntity::ID => 5,
			CommentEntity::USER_ID => 2,
		]);

		$this->commentRepository->method('getCurrentUserInfo')->willReturn(['id' => 666]);
		$this->commentRepository->method('getById')->willReturn(CommentEntity::from([
			CommentEntity::ID => 5,
			CommentEntity::STATUS_ID => 10,
			CommentEntity::USER_ID => 2,
		]));
		$this->statusRepository->method('getBasicInfoById')->willReturn(\Breeze\Entity\StatusEntity::from([
			\Breeze\Entity\StatusEntity::ID => 10,
			\Breeze\Entity\StatusEntity::WALL_ID => 666,
			\Breeze\Entity\StatusEntity::USER_ID => 2,
		]));

		$this->validateAllow->expects($this->once())
			->method('permissions')
			->with('deleteProfileComments', 'deleteStatus');

		$this->deleteComment->checkAllow();
	}

	public function testCheckAllowAsGeneralAdmin(): void
	{
		$this->deleteComment->setData([
			CommentEntity::ID => 6,
			CommentEntity::USER_ID => 2,
		]);

		$this->commentRepository->method('getCurrentUserInfo')->willReturn(['id' => 999]);
		$this->commentRepository->method('getById')->willReturn(CommentEntity::from([
			CommentEntity::ID => 6,
			CommentEntity::STATUS_ID => 10,
			CommentEntity::USER_ID => 2,
		]));
		$this->statusRepository->method('getBasicInfoById')->willReturn(\Breeze\Entity\StatusEntity::from([
			\Breeze\Entity\StatusEntity::ID => 10,
			\Breeze\Entity\StatusEntity::WALL_ID => 666,
			\Breeze\Entity\StatusEntity::USER_ID => 2,
		]));

		$this->validateAllow->expects($this->once())
			->method('permissions')
			->with('deleteComments', 'deleteStatus');

		$this->deleteComment->checkAllow();
	}
}
