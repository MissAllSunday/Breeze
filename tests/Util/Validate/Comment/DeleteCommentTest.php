<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Comment;

use Breeze\Entity\CommentEntity as CommentEntity;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\Comment\DeleteComment;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteCommentTest extends TestCase
{
	private CommentRepositoryInterface | MockObject $commentRepository;

	private Allow | MockObject $validateAllow;

	private User | MockObject $validateUser;

	private Data | MockObject $validateData;

	private DeleteComment $deleteComment;

	/**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		$this->commentRepository = $this->createMock(CommentRepositoryInterface::class);
		$this->validateAllow = $this->createMock(Allow::class);
		$this->validateUser = $this->createMock(User::class);
		$this->validateData = $this->createMock(Data::class);

		$this->deleteComment = new DeleteComment(
			$this->validateData,
			$this->validateUser,
			$this->validateAllow,
			$this->commentRepository
		);
	}

	public function testGetParams(): void
	{
		$this->assertEquals([
			'id' => 0,
			'userId' => 0,
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
}
