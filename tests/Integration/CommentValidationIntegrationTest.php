<?php

declare(strict_types=1);

namespace Breeze\Integration;

use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Fixtures\CommentFixtures;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\Comment\DeleteComment;
use Breeze\Util\Validate\Validations\Comment\PostComment;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CommentValidationIntegrationTest extends TestCase
{
    private CommentRepositoryInterface | MockObject $commentRepository;
    private Allow | MockObject $validateAllow;
    private User | MockObject $validateUser;
    private Data | MockObject $validateData;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
    {
		$this->commentRepository = $this->createMock(CommentRepositoryInterface::class);
		$this->validateAllow = $this->createMock(Allow::class);
		$this->validateUser = $this->createMock(User::class);
		$this->validateData = $this->createMock(Data::class);
	}

    public function testPostCommentValidation(): void
    {
        $postComment = new PostComment(
            $this->validateData,
            $this->validateUser,
            $this->validateAllow,
            $this->commentRepository
        );

        $validData = CommentFixtures::forInsertion();

        $this->validateData
            ->expects($this->once())
            ->method('validate')
            ->with($validData);

        $this->validateUser
            ->expects($this->once())
            ->method('validate');

        $this->validateAllow
            ->expects($this->once())
            ->method('permissions');

        $postComment->setData($validData);
        $postComment->validate();
    }

    public function testDeleteCommentValidation(): void
    {
        $deleteComment = new DeleteComment(
            $this->validateData,
            $this->validateUser,
            $this->validateAllow,
            $this->commentRepository
        );

        $deleteData = [
            'id' => 666,
            'userId' => 2,
        ];

        $this->commentRepository
            ->expects($this->once())
            ->method('getCurrentUserInfo')
            ->willReturn(['id' => 2]);

        $this->validateAllow
            ->expects($this->once())
            ->method('permissions');

        $deleteComment->setData($deleteData);
        $deleteComment->checkAllow();
    }

    public function testDeleteCommentValidationFailsWithoutPermission(): void
    {
        $deleteComment = new DeleteComment(
            $this->validateData,
            $this->validateUser,
            $this->validateAllow,
            $this->commentRepository
        );

        $deleteData = [
            'id' => 666,
            'userId' => 2,
        ];

        $this->commentRepository
            ->expects($this->once())
            ->method('getCurrentUserInfo')
            ->willReturn(['id' => 3]); // Different user

        $this->validateAllow
            ->expects($this->once())
            ->method('permissions')
            ->willThrowException(new NotAllowedException());

        $this->expectException(NotAllowedException::class);

        $deleteComment->setData($deleteData);
        $deleteComment->checkAllow();
    }
}
