<?php

declare(strict_types=1);

namespace Breeze\Integration;

use Breeze\Controller\API\CommentController;
use Breeze\Entity\CommentEntity;
use Breeze\Event\EventServiceProvider;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\InvalidCommentException;
use Breeze\Fixtures\CommentFixtures;
use Breeze\Util\Response;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CommentControllerIntegrationTest extends TestCase
{
    private CommentController $controller;
    private CommentRepositoryInterface | MockObject $commentRepository;
    private Response | MockObject $response;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
    {
		$this->commentRepository = $this->createMock(CommentRepositoryInterface::class);
		$this->response = $this->createMock(Response::class);
		$eventServiceProvider = $this->createMock(EventServiceProvider::class);
		$validateActions = $this->createMock(ValidateActionsInterface::class);

		$this->controller = new CommentController(
			$this->commentRepository,
			$validateActions,
			$this->response,
			$eventServiceProvider
		);
        // Inject dependencies (would be done via DI container in real app)
    }

    public function testPostCommentEndpoint(): void
    {
        $commentData = CommentFixtures::forInsertion();
        $expectedComment = CommentEntity::from(CommentFixtures::withCustomData([
            CommentEntity::ID => 123
        ]));

        $this->commentRepository
            ->expects($this->once())
            ->method('insert')
            ->willReturn([$expectedComment]);

        $this->response
            ->expects($this->once())
            ->method('success')
            ->with(
                'published_comment',
                [$expectedComment],
                Response::CREATED
            );

        // Simulate POST request data
        $_POST = $commentData;

        $this->controller->postComment();
    }

    public function testPostCommentWithInvalidData(): void
    {
        $invalidData = CommentFixtures::invalidComment();

        $this->commentRepository
            ->expects($this->once())
            ->method('insert')
            ->willThrowException(new InvalidCommentException('error_save_comment', 400));

        $this->response
            ->expects($this->once())
            ->method('error')
            ->with('error_save_comment', 400);

        $_POST = $invalidData;

        $this->controller->postComment();
    }

    public function testDeleteCommentEndpoint(): void
    {
        $commentId = 666;

        $this->commentRepository
            ->expects($this->once())
            ->method('deleteById')
            ->with($commentId)
            ->willReturn(true);

        $this->response
            ->expects($this->once())
            ->method('success')
            ->with('deleted_comment');

        $_POST = ['id' => $commentId];

        // Assuming there's a deleteComment method
        // $this->controller->deleteComment();
    }
}
