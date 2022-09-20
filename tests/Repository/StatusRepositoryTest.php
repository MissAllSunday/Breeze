<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Exceptions\InvalidCommentException;
use Breeze\Exceptions\InvalidStatusException;
use Breeze\Model\StatusModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StatusRepositoryTest extends TestCase
{
	/**
	 * @var StatusModel&MockObject
	 */
	private $statusModel;

	/**
	 * @var CommentRepository&MockObject
	 */
	private $commentRepository;

	private StatusRepository $statusRepository;

	public function setUp(): void
	{
		$this->statusModel =  $this->createMock(StatusModel::class);

		$this->commentRepository = $this->createMock(CommentRepository::class);

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

		if ($newId === 0) {
			$this->expectException(InvalidStatusException::class);
		}

		$newStatusId = $this->statusRepository->save($dataToInsert);

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
	 * @throws InvalidStatusException
	 */
	public function testGetByProfile(int $profileOwnerId, array $statusByProfileWillReturn): void
	{
		if ($profileOwnerId !== 1) {
			$this->statusModel
				->expects($this->once())
				->method('getStatusByProfile')
				->with([
					'start' => 0,
					'maxIndex' => 0,
					'ids' => [$profileOwnerId],
				])
				->willReturn($statusByProfileWillReturn);
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
					'some data',
				],
			],
			'no data' => [
				'profileOwnerId' => 2,
				'statusByProfileWillReturn' => [],
			],
			'data from query' => [
				'profileOwnerId' => 3,
				'statusByProfileWillReturn' => [
					'some data',
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
		$this->statusModel
			->expects($this->once())
			->method('getById')
			->with($statusId)
			->willReturn($getByIdWillReturn);

		if (empty($getByIdWillReturn)) {
			$this->expectException(InvalidStatusException::class);
		}

		$statusById = $this->statusRepository->getById($statusId);

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
			$this->commentRepository
				->expects($this->once())
				->method('deleteByStatusId')
				->with($statusId)
				->willReturn($commentDeleteByStatusId);

		if ($statusId !== 1 && !$commentDeleteByStatusId) {
			$this->expectException(InvalidCommentException::class);
		}

		$this->statusModel->expects($this->once())
			->method('delete')
			->with([$statusId])
			->willReturn($deleteByStatusId);

		if (!$deleteByStatusId) {
			$this->expectException(InvalidStatusException::class);
		}


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
