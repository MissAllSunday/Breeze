<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Model\CommentModel;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Override time() in the current namespace for testing.
 *
 */
function time(): int
{
	return 581299200;
}

class CommentRepositoryTest extends TestCase
{
	use ProphecyTrait;

	#[DataProvider('saveProvider')]
	public function testSave(array $dataToInsert, int $newId): void
	{
		$commentModel = $this->prophesize(CommentModel::class);
		$likeRepository = $this->createMock(LikeRepositoryInterface::class);
		$commentRepository = new CommentRepository($commentModel->reveal(), $likeRepository);

		$commentModel
			->insert($dataToInsert)
			->willReturn($newId);

		if ($newId === 0) {
			$this->expectException(InvalidCommentException::class);
		}

		$newCommentId = $commentRepository->save($dataToInsert);

		$this->assertEquals($newId, $newCommentId);
	}

	public static function saveProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'dataToInsert' => [
					'createdAt' => 581299200,
					'likes' => 0,
				],
				'newId' => 666,
			],
			'InvalidCommentException' => [
				'dataToInsert' => [
					'createdAt' => 581299200,
					'likes' => 0,
				],
				'newId' => 0,
			],
		];
	}

	#[DataProvider('getByProfileProvider')]
	public function testGetByProfile(
		array $userProfiles,
		array $commentModelReturn,
		array $commentsByProfileWillReturn
	): void {
		$commentModel = $this->prophesize(CommentModel::class);
		$likeRepository = $this->createMock(LikeRepositoryInterface::class);
		$commentRepository = new CommentRepository($commentModel->reveal(), $likeRepository);

			$commentModel
				->getByProfiles($userProfiles)
				->willReturn($commentModelReturn);

		$likeRepository
			->method('appendLikeData')
			->willReturn($commentModelReturn['data'][1]);

		$commentsByProfile = $commentRepository->getByProfile($userProfiles);

		$this->assertEquals($commentsByProfileWillReturn, $commentsByProfile);
	}

	public static function getByProfileProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'userProfiles' => [1],
				'commentModelReturn' => [
					'usersIds' => [1,2,3],
					'data' => [1 => [ 1 => [
						'id' => 1,
						'statusId' => 1,
						'userId' => 1,
						'createdAt' => 581299200,
						'body' => 'comment body',
						'likes' => 0,
					]]],
				],
				'commentsByProfileWillReturn' => [
					1 => [
						1 => [
							'id' => 1,
							'statusId' => 1,
							'userId' => 1,
							'createdAt' => 581299200,
							'body' => 'comment body',
							'likes' => 0,
							'userData' => [
								'link' => 'Guest',
								'name' => 'Guest',
								'avatar' => ['href' => 'avatar_url/default.png'],
							],
						],
					],],
			],
		];
	}

	#[DataProvider('getByStatusProvider')]
	public function testGetByStatus(array $statusId, array $commentsBystatusWillReturn): void
	{
		$commentModel = $this->prophesize(CommentModel::class);
		$likeRepository = $this->createMock(LikeRepositoryInterface::class);
		$commentRepository = new CommentRepository($commentModel->reveal(), $likeRepository);

		$commentModel
			->getByStatus($statusId)
			->willReturn($commentsBystatusWillReturn);

		$commentsByStatus = $commentRepository->getByStatus($statusId);

		$this->assertEquals($commentsBystatusWillReturn, $commentsByStatus);
	}

	public static function getByStatusProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'statusId' => [1],
				'commentsByStatusWillReturn' => [
					'some data',
				],
			],
			'no data' => [
				'statusId' => [],
				'commentsByStatusWillReturn' => [],
			],
		];
	}
}
