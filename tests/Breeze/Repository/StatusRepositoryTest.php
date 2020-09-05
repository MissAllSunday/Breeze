<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Model\StatusModel;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StatusRepositoryTest extends TestCase
{
	/**
	 * @var MockBuilder|StatusModel
	 */
	private  $statusModel;

	/**
	 * @var MockBuilder|CommentRepository
	 */
	private $commentRepository;

	private StatusRepository $statusRepository;

	public function setUp(): void
	{
		$this->statusModel = $this->getMockInstance(StatusModel::class);
		$this->commentRepository = $this->getMockInstance(CommentRepository::class);

		$this->statusRepository = new StatusRepository($this->statusModel, $this->commentRepository);
	}

	/**
	 * @dataProvider saveProvider
	 * @throws InvalidStatusException
	 */
	public function testSave(array $dataToInsert, int $newId): void
	{
		$this->statusModel
			->expects($this->once())
			->method('insert')
			->with($dataToInsert)
			->willReturn($newId);

		if (0 === $newId)
			$this->expectException(InvalidStatusException::class);

		$newStatusId = $this->statusRepository->save($dataToInsert);

		$this->assertEquals($newId, $newStatusId);

	}

	public function saveProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'dataToInsert' => [
					'some data'
				],
				'newId' => 666,
			],
			'InvalidStatusException' => [
				'dataToInsert' => [
					'some data'
				],
				'newId' => 0,
			],
		];
	}

	/**
	 * @dataProvider getByProfileProvider
	 * @throws InvalidStatusException
	 */
	public function testGetByProfile(int $profileOwnerId, array $statusByProfileWillReturn): void
	{
		if (1 !== $profileOwnerId)
		{
			$this->statusModel
				->expects($this->once())
				->method('getStatusByProfile')
				->with([
					'start' => 0,
					'maxIndex' => 0,
					'ids' => [$profileOwnerId]
				])
				->willReturn($statusByProfileWillReturn);

			if (empty($statusByProfileWillReturn))
				$this->expectException(InvalidStatusException::class);
		}

		$statusByProfile = $this->statusRepository->getByProfile($profileOwnerId);

		$this->assertEquals($statusByProfileWillReturn, $statusByProfile);
	}

	public function getByProfileProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'profileOwnerId' => 1,
				'statusByProfileWillReturn' => [
					'some data'
				],
			],
			'InvalidStatusException' => [
				'profileOwnerId' => 2,
				'statusByProfileWillReturn' => [],
			],
			'data from query' => [
				'profileOwnerId' => 3,
				'statusByProfileWillReturn' => [
					'some data'
				],
			],
		];
	}

	/**
	 * @dataProvider deleteByIdProvider
	 * @throws InvalidStatusException
	 */
	public function testDeleteById(int $statusId, bool $deleteByStatusId, bool $commentDeleteByStatusId): void
	{
			$this->commentRepository
				->expects($this->once())
				->method('deleteByStatusId')
				->with($statusId)
				->willReturn($commentDeleteByStatusId);

		if (1 !== $statusId && !$commentDeleteByStatusId)
			$this->expectException(InvalidCommentException::class);

		$this->statusModel->expects($this->once())
			->method('delete')
			->with([$statusId])
			->willReturn($deleteByStatusId);

			if (!$deleteByStatusId)
				$this->expectException(InvalidStatusException::class);


		$deleteById = $this->statusRepository->deleteById($statusId);

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

	protected function getMockInstance(string $class): MockObject
	{
		return $this->getMockBuilder($class)
			->disableOriginalConstructor()
			->getMock();
	}
}
