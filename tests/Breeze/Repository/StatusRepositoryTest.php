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

	protected function getMockInstance(string $class): MockObject
	{
		return $this->getMockBuilder($class)
			->disableOriginalConstructor()
			->getMock();
	}
}
