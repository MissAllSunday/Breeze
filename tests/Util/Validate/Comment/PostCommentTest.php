<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Comment;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\StatusEntity;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\Comment\PostComment;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PostCommentTest extends TestCase
{
	public function testGetParams(): void
	{
		$commentRepository = $this->createStub(CommentRepositoryInterface::class);
		$statusRepository = $this->createStub(StatusRepositoryInterface::class);
		$validateAllow = $this->createStub(Allow::class);
		$validateUser = $this->createStub(User::class);
		$validateData = $this->createStub(Data::class);

		$postComment = new PostComment(
			$validateData,
			$validateUser,
			$validateAllow,
			$commentRepository,
			$statusRepository
		);

		$this->assertEquals([
			'body' => '',
			'status_id' => 0,
			'user_id' => 0,
		], $postComment->getParams());
	}

	#[DataProvider('checkAllowProvider')]
	public function testCheckAllow(array $data, int $currentUserId, int $wallId, bool $isExpectedException): void
	{
		$commentRepository = $this->createStub(CommentRepositoryInterface::class);
		$commentRepository->method('getCurrentUserInfo')->willReturn(['id' => $currentUserId]);

		$statusRepository = $this->createStub(StatusRepositoryInterface::class);
		$statusRepository->method('getBasicInfoById')->willReturn(
			StatusEntity::from([
				StatusEntity::ID => $data[CommentEntity::STATUS_ID],
				StatusEntity::WALL_ID => $wallId,
				StatusEntity::USER_ID => 1,
			])
		);

		$validateAllow = $this->createMock(Allow::class);

		if ($currentUserId === $wallId) {
			$validateAllow->expects($this->never())->method('permissions');
		} elseif ($isExpectedException) {
			$validateAllow->expects($this->once())
				->method('permissions')
				->with('postComments', 'postComments')
				->willThrowException(new NotAllowedException());
			$this->expectException(NotAllowedException::class);
		} else {
			$validateAllow->expects($this->once())
				->method('permissions')
				->with('postComments', 'postComments');
		}

		$validateUser = $this->createStub(User::class);
		$validateData = $this->createStub(Data::class);

		$postComment = new PostComment($validateData, $validateUser, $validateAllow, $commentRepository, $statusRepository);
		$postComment->setData($data);

		$postComment->checkAllow();
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
				'currentUserId' => 1,
				'wallId' => 1,
				'isExpectedException' => false,
			],
			'non-owner with post comment permission' => [
				'data' => [
					CommentEntity::STATUS_ID => 5,
					CommentEntity::USER_ID => 1,
					CommentEntity::BODY => 'Test',
				],
				'currentUserId' => 1,
				'wallId' => 2,
				'isExpectedException' => false,
			],
			'non-owner without post comment permission' => [
				'data' => [
					CommentEntity::STATUS_ID => 5,
					CommentEntity::USER_ID => 1,
					CommentEntity::BODY => 'Test',
				],
				'currentUserId' => 1,
				'wallId' => 2,
				'isExpectedException' => true,
			],
		];
	}
}
