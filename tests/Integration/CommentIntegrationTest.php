<?php

declare(strict_types=1);

namespace Breeze\Integration;

use Breeze\Entity\CommentEntity;
use Breeze\Repository\CommentRepository;
use Breeze\Repository\InvalidCommentException;
use Breeze\Fixtures\CommentFixtures;
use Breeze\Util\Validate\DataNotFoundException;
use PHPUnit\Framework\TestCase;

class CommentIntegrationTest extends TestCase
{
    private CommentRepository $commentRepository;

    protected function setUp(): void
    {
        // Setup would typically initialize real database connection
        // For this example, we'll mock the dependencies
        $this->commentRepository = $this->createMock(CommentRepository::class);
    }

    public function testCreateCommentSuccessfully(): void
    {
        $commentData = CommentFixtures::forInsertion();
        $commentEntity = CommentEntity::from($commentData);

        $expectedResult = [
            CommentEntity::from(CommentFixtures::withCustomData([
                CommentEntity::ID => 123
            ]))
        ];

        $this->commentRepository
            ->expects($this->once())
            ->method('insert')
            ->with($this->equalTo($commentEntity))
            ->willReturn($expectedResult);

        $result = $this->commentRepository->insert($commentEntity);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(CommentEntity::class, $result[0]);
        $this->assertEquals(123, $result[0]->getId());
        $this->assertEquals($commentData[CommentEntity::BODY], $result[0]->getBody());
    }

    public function testCreateCommentWithInvalidData(): void
    {
        $invalidData = CommentFixtures::invalidComment();
        $commentEntity = CommentEntity::from($invalidData);

        $this->commentRepository
            ->expects($this->once())
            ->method('insert')
            ->with($this->equalTo($commentEntity))
            ->willThrowException(new InvalidCommentException('error_save_comment'));

        $this->expectException(InvalidCommentException::class);
        $this->expectExceptionMessage('error_save_comment');

        $this->commentRepository->insert($commentEntity);
    }

    public function testDeleteCommentById(): void
    {
        $commentId = 666;

        $this->commentRepository
            ->expects($this->once())
            ->method('deleteById')
            ->with($commentId)
            ->willReturn(true);

        $result = $this->commentRepository->deleteById($commentId);

        $this->assertTrue($result);
    }

    public function testDeleteNonExistentComment(): void
    {
        $nonExistentId = 999;

        $this->commentRepository
            ->expects($this->once())
            ->method('deleteById')
            ->with($nonExistentId)
            ->willThrowException(new DataNotFoundException('Comment not found'));

        $this->expectException(DataNotFoundException::class);
        $this->expectExceptionMessage('Comment not found');

        $this->commentRepository->deleteById($nonExistentId);
    }

    public function testDeleteCommentsByStatusId(): void
    {
        $statusId = 10;

        $this->commentRepository
            ->expects($this->once())
            ->method('deleteByStatusId')
            ->with($statusId)
            ->willReturn(true);

        $result = $this->commentRepository->deleteByStatusId($statusId);

        $this->assertTrue($result);
    }

    public function testGetCommentById(): void
    {
        $commentId = 666;
        $expectedComment = CommentEntity::from(CommentFixtures::basic());

        $this->commentRepository
            ->expects($this->once())
            ->method('getById')
            ->with($commentId)
            ->willReturn($expectedComment);

        $result = $this->commentRepository->getById($commentId);

        $this->assertEquals($expectedComment, $result);
        $this->assertEquals($commentId, $result->getId());
    }

    public function testCreateMultipleComments(): void
    {
        $commentsData = CommentFixtures::multipleComments();
        $results = [];

        foreach ($commentsData as $index => $commentData) {
            $commentEntity = CommentEntity::from($commentData);
            $expectedId = $commentData[CommentEntity::ID];

            $this->commentRepository
                ->expects($this->at($index))
                ->method('insert')
                ->with($this->equalTo($commentEntity))
                ->willReturn([CommentEntity::from($commentData)]);

            $result = $this->commentRepository->insert($commentEntity);
            $results[] = $result[0];
        }

        $this->assertCount(3, $results);
        foreach ($results as $index => $comment) {
            $this->assertInstanceOf(CommentEntity::class, $comment);
            $this->assertEquals($commentsData[$index][CommentEntity::ID], $comment->getId());
        }
    }
}
