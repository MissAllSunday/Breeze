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

	/**
	 * @dataProvider saveProvider
	 * @throws InvalidCommentException
	 */
	public function testSave(array $dataToInsert, int $newId): void
	{
		$commentModel = $this->prophesize(CommentModel::class);
		$commentRepository = new CommentRepository($commentModel->reveal());

		$commentModel
			->insert($dataToInsert)
			->willReturn($newId);

		if ($newId === 0) {
			$this->expectException(InvalidCommentException::class);
		}

		$newCommentId = $commentRepository->save($dataToInsert);

		$this->assertEquals($newId, $newCommentId);
	}

	public function saveProvider(): array
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

	/**
	 * @dataProvider getByProfileProvider
	 */
	public function testGetByProfile(int $profileOwnerId, array $commentsByProfileWillReturn): void
	{
		$commentModel = $this->prophesize(CommentModel::class);
		$commentRepository = new CommentRepository($commentModel->reveal());

			$commentModel
				->getByProfiles([$profileOwnerId])
				->willReturn($commentsByProfileWillReturn);

		$commentsByProfile = $commentRepository->getByProfile($profileOwnerId);

		$this->assertEquals($commentsByProfileWillReturn, $commentsByProfile);
	}

	public function getByProfileProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'profileOwnerId' => 1,
				'commentsByProfileWillReturn' => [
					'usersIds' => [1],
					'data' => [
						666 => [
							'id' => 666,
							'statusId' => 666,
							'userId' => 1,
							'createdAt' => time(),
							'body' => 'comment body',
							'likes' => [],
						],],
				],
			],
			'no data' => [
				'profileOwnerId' => 2,
				'commentsByProfileWillReturn' => [],
			],
		];
	}

	/**
	 * @dataProvider getByStatusProvider
	 */
	public function testGetByStatus(array $statusId, array $commentsBystatusWillReturn): void
	{
		$commentModel = $this->prophesize(CommentModel::class);
		$commentRepository = new CommentRepository($commentModel->reveal());

		$commentModel
			->getByStatus($statusId)
			->willReturn($commentsBystatusWillReturn);

		$commentsByStatus = $commentRepository->getByStatus($statusId);

		$this->assertEquals($commentsBystatusWillReturn, $commentsByStatus);
	}

	public function getByStatusProvider(): array
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
