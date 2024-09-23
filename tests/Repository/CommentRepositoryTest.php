<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Model\CommentModel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

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
	#[DataProvider('saveProvider')]
	public function testSave(array $dataToInsert, int $newId): void
	{
		$commentModel = $this->createMock(CommentModel::class);
		$likeRepository = $this->createMock(LikeRepositoryInterface::class);
		$commentRepository = new CommentRepository($commentModel, $likeRepository);

		$commentModel
			->method('insert')
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
		$commentModel = $this->createMock(CommentModel::class);
		$likeRepository = $this->createMock(LikeRepositoryInterface::class);
		$commentRepository = new CommentRepository($commentModel, $likeRepository);

			$commentModel
				->method('getByProfiles')
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
	public function testGetByStatus(array $statusId, array $commentsByStatusWillReturn): void
	{
		$commentModel = $this->createMock(CommentModel::class);
		$likeRepository = $this->createMock(LikeRepositoryInterface::class);
		$commentRepository = new CommentRepository($commentModel, $likeRepository);

		$commentModel
			->method('getByStatus')
			->willReturn($commentsByStatusWillReturn);

		$commentsByStatus = $commentRepository->getByStatus($statusId);

		$this->assertEquals($commentsByStatusWillReturn, $commentsByStatus);
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
