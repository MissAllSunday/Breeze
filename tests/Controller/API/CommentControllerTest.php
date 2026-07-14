<?php

declare(strict_types=1);

namespace Breeze\Controller\API;

use Breeze\Entity\CommentEntity;
use Breeze\Repository\InvalidCommentException;
use Breeze\Service\CommentServiceInterface;
use Breeze\Service\SecurityServiceInterface;
use Breeze\Util\ResponseInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class CommentControllerTest extends TestCase
{
	private CommentController $commentController;

	private CommentServiceInterface | MockObject $commentService;

	private ResponseInterface | MockObject $response;

	private SecurityServiceInterface | MockObject $security;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->commentService = $this->createMock(CommentServiceInterface::class);
		$validateActions = $this->createMock(ValidateActionsInterface::class);
		$this->response = $this->createMock(ResponseInterface::class);
		$this->security = $this->createMock(SecurityServiceInterface::class);

		$this->commentController = new CommentController(
			$this->commentService,
			$validateActions,
			$this->response,
			$this->security
		);
	}

	public function testGetSubActions(): void
	{
		$this->assertEquals(CommentController::SUB_ACTIONS, $this->commentController->getSubActions());
	}

	public function testPostCommentSuccess(): void
	{
		$commentData = [
			CommentEntity::STATUS_ID => 123,
			CommentEntity::BODY => 'Test comment',
			CommentEntity::USER_ID => 1,
		];
		$expectedEntities = [
			CommentEntity::from([
				CommentEntity::ID => 1,
				CommentEntity::STATUS_ID => 123,
				CommentEntity::BODY => 'Test comment',
				CommentEntity::USER_ID => 1,
			]),
		];

		$reflection = new \ReflectionClass($this->commentController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setValue($this->commentController, $commentData);

		$this->commentService->expects($this->once())
			->method('save')
			->with($commentData)
			->willReturn($expectedEntities);

		$this->response->expects($this->once())
			->method('success')
			->with('published_comment', $expectedEntities, ResponseInterface::CREATED);

		$this->commentController->postComment();
	}

	public function testPostCommentThrowsInvalidCommentException(): void
	{
		$commentData = [
			CommentEntity::STATUS_ID => 123,
			CommentEntity::BODY => '',
		];
		$errorMessage = 'Invalid comment data';

		$reflection = new \ReflectionClass($this->commentController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setValue($this->commentController, $commentData);

		$exception = new InvalidCommentException($errorMessage);

		$this->commentService->expects($this->once())
			->method('save')
			->willThrowException($exception);

		$this->response->expects($this->once())
			->method('error')
			->with($errorMessage, InvalidCommentException::STATUS_CODE);

		$this->commentController->postComment();
	}

	public function testDeleteCommentSuccess(): void
	{
		$commentId = 42;

		$reflection = new \ReflectionClass($this->commentController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setValue($this->commentController, [CommentEntity::ID => $commentId]);

		$this->commentService->expects($this->once())
			->method('deleteById')
			->with($commentId);

		$this->response->expects($this->once())
			->method('success')
			->with('deleted_comment', [], ResponseInterface::OK);

		$this->commentController->deleteComment();
	}

	public function testDeleteCommentThrowsDataNotFoundException(): void
	{
		$commentId = 999;
		$errorMessage = 'Comment not found';

		$reflection = new \ReflectionClass($this->commentController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setValue($this->commentController, [CommentEntity::ID => $commentId]);

		$this->commentService->expects($this->once())
			->method('deleteById')
			->willThrowException(new DataNotFoundException($errorMessage));

		$this->response->expects($this->once())
			->method('error')
			->with($errorMessage);

		$this->commentController->deleteComment();
	}
}
