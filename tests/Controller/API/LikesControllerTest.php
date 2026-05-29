<?php

declare(strict_types=1);

namespace Breeze\Controller\API;

use Breeze\Entity\LikeEntity;
use Breeze\Entity\LikeInfoEntity;
use Breeze\Enums\LikesEnum;
use Breeze\Service\LikeServiceInterface;
use Breeze\Util\Response;
use Breeze\Util\Validate\InvalidDataException;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class LikesControllerTest extends TestCase
{
	private LikesController $likesController;

	private LikeServiceInterface | MockObject $likeService;

	private ValidateActionsInterface | MockObject $validateActions;

	private Response | MockObject $response;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->likeService = $this->createMock(LikeServiceInterface::class);
		$this->validateActions = $this->createMock(ValidateActionsInterface::class);
		$this->response = $this->createMock(Response::class);

		$this->likesController = new LikesController(
			$this->likeService,
			$this->validateActions,
			$this->response
		);
	}

	public function testGetSubActions(): void
	{
		$this->assertEquals(LikesController::SUB_ACTIONS, $this->likesController->getSubActions());
	}

	public function testLikeSuccess(): void
	{
		$likeData = [
			LikeEntity::TYPE => LikesEnum::Status->value,
			LikeEntity::ID => 123,
			LikeEntity::ID_MEMBER => 1,
		];
		$expectedLikeInfo = LikeInfoEntity::from([
			LikeEntity::ID => 123,
			LikeEntity::TYPE => LikesEnum::Status->value,
			LikeInfoEntity::TEXT => 'Test',
			LikeInfoEntity::HREF => 'https://example.com',
			LikeInfoEntity::LIKES => [],
		]);

		$reflection = new \ReflectionClass($this->likesController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setAccessible(true);
		$dataProperty->setValue($this->likesController, $likeData);

		$this->likeService->expects($this->once())
			->method('likeContent')
			->with(
				LikesEnum::Status,
				$likeData[LikeEntity::ID],
				$likeData[LikeEntity::ID_MEMBER]
			)
			->willReturn($expectedLikeInfo);

		$this->response->expects($this->once())
			->method('success')
			->with('likeSuccess', $expectedLikeInfo, Response::CREATED);

		$this->likesController->like();
	}

	public function testLikeWithCommentsType(): void
	{
		$likeData = [
			LikeEntity::TYPE => LikesEnum::Comments->value,
			LikeEntity::ID => 456,
			LikeEntity::ID_MEMBER => 2,
		];
		$expectedLikeInfo = LikeInfoEntity::from([
			LikeEntity::ID => 456,
			LikeEntity::TYPE => LikesEnum::Comments->value,
			LikeInfoEntity::TEXT => 'Test',
			LikeInfoEntity::HREF => 'https://example.com',
			LikeInfoEntity::LIKES => [],
		]);

		$reflection = new \ReflectionClass($this->likesController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setAccessible(true);
		$dataProperty->setValue($this->likesController, $likeData);

		$this->likeService->expects($this->once())
			->method('likeContent')
			->with(
				LikesEnum::Comments,
				$likeData[LikeEntity::ID],
				$likeData[LikeEntity::ID_MEMBER]
			)
			->willReturn($expectedLikeInfo);

		$this->response->expects($this->once())
			->method('success')
			->with('likeSuccess', $expectedLikeInfo, Response::CREATED);

		$this->likesController->like();
	}

	public function testLikeThrowsInvalidDataException(): void
	{
		$likeData = [
			LikeEntity::TYPE => LikesEnum::Status->value,
			LikeEntity::ID => 999,
			LikeEntity::ID_MEMBER => 1,
		];
		$errorMessage = 'Invalid like data';

		$reflection = new \ReflectionClass($this->likesController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setAccessible(true);
		$dataProperty->setValue($this->likesController, $likeData);

		$exception = new InvalidDataException($errorMessage);

		$this->likeService->expects($this->once())
			->method('likeContent')
			->willThrowException($exception);

		$this->response->expects($this->once())
			->method('error')
			->with($errorMessage, InvalidDataException::STATUS_CODE);

		$this->likesController->like();
	}
}
