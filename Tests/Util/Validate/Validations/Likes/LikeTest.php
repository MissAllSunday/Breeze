<?php

declare(strict_types=1);

namespace Breeze\Tests\Util\Validate\Validations\Likes;

use Breeze\Entity\LikeEntity;
use Breeze\Enums\LikesEnum;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\Likes\Like;
use PHPUnit\Framework\TestCase;

class LikeTest extends TestCase
{
	public function testCheckContentExistsStatusNotFound(): void
	{
		$this->expectException(DataNotFoundException::class);
		$this->expectExceptionMessage('error_no_data');

		$statusRepository = $this->createMock(\Breeze\Repository\StatusRepositoryInterface::class);
		$commentRepository = $this->createMock(\Breeze\Repository\CommentRepositoryInterface::class);

		$statusRepository->method('getById')
			->with(123)
			->willThrowException(new DataNotFoundException('error_no_data'));

		$likeValidator = new Like(
			[
				LikeEntity::ID => 123,
				LikeEntity::TYPE => LikesEnum::Status->value,
				LikeEntity::ID_MEMBER => 456,
			],
			null,
			null,
			null,
			$statusRepository,
			$commentRepository
		);

		$likeValidator->checkContentExists();
	}

	public function testCheckContentExistsCommentNotFound(): void
	{
		$this->expectException(DataNotFoundException::class);
		$this->expectExceptionMessage('error_no_data');

		$statusRepository = $this->createMock(\Breeze\Repository\StatusRepositoryInterface::class);
		$commentRepository = $this->createMock(\Breeze\Repository\CommentRepositoryInterface::class);

		$commentRepository->method('getById')
			->with(123)
			->willThrowException(new DataNotFoundException('error_no_data'));

		$likeValidator = new Like(
			[
				LikeEntity::ID => 123,
				LikeEntity::TYPE => LikesEnum::Comments->value,
				LikeEntity::ID_MEMBER => 456,
			],
			null,
			null,
			null,
			$statusRepository,
			$commentRepository
		);

		$likeValidator->checkContentExists();
	}

	public function testCheckContentExistsStatusFound(): void
	{
		$statusRepository = $this->createMock(\Breeze\Repository\StatusRepositoryInterface::class);
		$commentRepository = $this->createMock(\Breeze\Repository\CommentRepositoryInterface::class);

		$statusRepository->method('getById')
			->with(123)
			->willReturn(null);

		$likeValidator = new Like(
			[
				LikeEntity::ID => 123,
				LikeEntity::TYPE => LikesEnum::Status->value,
				LikeEntity::ID_MEMBER => 456,
			],
			null,
			null,
			null,
			$statusRepository,
			$commentRepository
		);

		$this->assertNull($likeValidator->checkContentExists());
	}

	public function testCheckContentExistsCommentFound(): void
	{
		$statusRepository = $this->createMock(\Breeze\Repository\StatusRepositoryInterface::class);
		$commentRepository = $this->createMock(\Breeze\Repository\CommentRepositoryInterface::class);

		$commentRepository->method('getById')
			->with(123)
			->willReturn(null);

		$likeValidator = new Like(
			[
				LikeEntity::ID => 123,
				LikeEntity::TYPE => LikesEnum::Comments->value,
				LikeEntity::ID_MEMBER => 456,
			],
			null,
			null,
			null,
			$statusRepository,
			$commentRepository
		);

		$this->assertNull($likeValidator->checkContentExists());
	}
}
