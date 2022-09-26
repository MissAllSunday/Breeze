<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Model\StatusModelInterface;
use Breeze\Util\Validate\DataNotFoundException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class StatusRepositoryTest extends TestCase
{
	use ProphecyTrait;

	/**
	 * @dataProvider saveProvider
	 * @throws InvalidStatusException
	 */
	public function testSave(array $dataToInsert, int $newId): void
	{
		$statusModel =  $this->prophesize(StatusModelInterface::class);
		$commentRepository = $this->prophesize(CommentRepositoryInterface::class);
		$likeRepository = $this->prophesize(LikeRepositoryInterface::class);

		$statusRepository = new StatusRepository(
			$statusModel->reveal(),
			$commentRepository->reveal(),
			$likeRepository->reveal()
		);

		$statusModel
			->insert($dataToInsert)
			->willReturn($newId);

		if ($newId === 0) {
			$this->expectException(InvalidStatusException::class);
		}

		$newStatusId = $statusRepository->save($dataToInsert);

		$this->assertEquals($newId, $newStatusId);
	}

	public function saveProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'dataToInsert' => [
					'some data',
				],
				'newId' => 666,
			],
			'InvalidStatusException' => [
				'dataToInsert' => [
					'some data',
				],
				'newId' => 0,
			],
		];
	}

	/**
	 * @dataProvider getByProfileProvider
	 * @throws DataNotFoundException
	 */
	public function testGetByProfile(
		int $profileOwnerId,
		array $statusModelWillReturn,
		array $commentsByProfileWillReturn,
		array $getByProfileReturn
	): void {
		$statusModel =  $this->prophesize(StatusModelInterface::class);
		$commentRepository = $this->prophesize(CommentRepositoryInterface::class);
		$likeRepository = $this->createMock(LikeRepositoryInterface::class);

		$statusRepository = new StatusRepository(
			$statusModel->reveal(),
			$commentRepository->reveal(),
			$likeRepository
		);

		$statusModel->getCount()->willReturn(1);
		$statusModel
			->getStatusByProfile([
				'start' => 0,
				'maxIndex' => 666,
				'ids' => [$profileOwnerId],
			])
			->willReturn($statusModelWillReturn);

		if ($profileOwnerId == 2) {
			$this->expectException(DataNotFoundException::class);
		}

		$commentRepository->getByProfile($profileOwnerId)->willReturn($commentsByProfileWillReturn);
		$likeRepository->method('appendLikeData')->willReturn($getByProfileReturn['status']);
		$statusByProfile = $statusRepository->getByProfile($profileOwnerId);

		$this->assertEquals($getByProfileReturn, $statusByProfile);
	}

	public function getByProfileProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'profileOwnerId' => 1,
				'statusModelWillReturn' => [
					'usersIds' => [1],
					'data' => [],
				],
				'commentsByProfileWillReturn' => [
					'usersIds' => [1],
					'data' => [],
				],
				'getByProfileReturn' => [
					'users' => [1 => [
						'link' => 'Guest',
						'name' => 'Guest',
						'avatar' => ['href' => 'avatar_url/default.png'],
					]],
					'status' => [
						1 => [
							'id' => 666,
							'wallId' => 666,
							'userId' => 1,
							'createdAt' => 581299200,
							'body' => 'some body',
							'likes' => [],
							'comments' => [],
						],],
				],
			],
			'no data' => [
				'profileOwnerId' => 2,
				'statusModelWillReturn' => [],
				'commentsByProfileWillReturn' => [],
				'getByProfileReturn' => [
					'users' => [],
					'status' => [],
				],
			],
		];
	}

	/**
	 * @dataProvider getByIdProvider
	 * @throws InvalidStatusException
	 */
	public function testGetById(int $statusId, array $getByIdWillReturn): void
	{
		$statusModel =  $this->prophesize(StatusModelInterface::class);
		$commentRepository = $this->prophesize(CommentRepositoryInterface::class);
		$likeRepository = $this->prophesize(LikeRepositoryInterface::class);

		$statusRepository = new StatusRepository(
			$statusModel->reveal(),
			$commentRepository->reveal(),
			$likeRepository->reveal()
		);

		$statusModel
			->getById($statusId)
			->willReturn($getByIdWillReturn);

		if (empty($getByIdWillReturn)) {
			$this->expectException(InvalidStatusException::class);
		}

		$statusById = $statusRepository->getById($statusId);

		$this->assertEquals($getByIdWillReturn, $statusById);
	}

	public function getByIdProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'statusId' => 1,
				'getByIdWillReturn' => [
					'some data',
				],
			],
			'InvalidStatusException' => [
				'statusId' => 2,
				'getByIdWillReturn' => [],
			],
		];
	}

	/**
	 * @dataProvider deleteByIdProvider
	 * @throws InvalidStatusException
	 */
	public function testDeleteById(int $statusId, bool $deleteByStatusId, bool $commentDeleteByStatusId): void
	{
		$statusModel =  $this->prophesize(StatusModelInterface::class);
		$commentRepository = $this->prophesize(CommentRepositoryInterface::class);
		$likeRepository = $this->prophesize(LikeRepositoryInterface::class);

		$statusRepository = new StatusRepository(
			$statusModel->reveal(),
			$commentRepository->reveal(),
			$likeRepository->reveal()
		);

			$commentRepository
				->deleteByStatusId($statusId)
				->willReturn($commentDeleteByStatusId);

		if ($statusId !== 1 && !$commentDeleteByStatusId) {
			$this->expectException(InvalidCommentException::class);
		}

		$statusModel
			->delete([$statusId])
			->willReturn($deleteByStatusId);

		if (!$deleteByStatusId) {
			$this->expectException(InvalidStatusException::class);
		}


		$deleteById = $statusRepository->deleteById($statusId);

		$this->assertEquals($deleteByStatusId, $deleteById);
	}

	public function deleteByIdProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'statusId' => 1,
				'deleteByStatusId' => true,
				'commentDeleteByStatusId' => true,
			],
			'InvalidStatusException' => [
				'statusId' => 2,
				'deleteByStatusId' => false,
				'commentDeleteByStatusId' => true,
			],
			'InvalidCommentException' => [
				'statusId' => 3,
				'deleteByStatusId' => false,
				'commentDeleteByStatusId' => false,
			],
		];
	}
}
