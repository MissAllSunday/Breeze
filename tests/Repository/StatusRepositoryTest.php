<?php

declare(strict_types=1);

namespace Breeze\Repository;

use Breeze\Database\ClientInterface;
use Breeze\Entity\StatusEntity;
use Breeze\Entity\StatusHandledEntity;
use Breeze\Util\Validate\DataNotFoundException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class StatusRepositoryTest extends TestCase
{
	private MockObject|ClientInterface $dbClient;

	private MockObject|LikeRepositoryInterface $likeRepository;

	private MockObject|CommentRepositoryInterface $commentRepository;

	private MockObject|StatusRepository $statusRepository;

	private stdClass $queryObject;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->dbClient = $this->createMock(ClientInterface::class);
		$this->likeRepository = $this->createMock(LikeRepositoryInterface::class);
		$this->commentRepository = $this->createMock(CommentRepositoryInterface::class);
		$this->statusRepository = $this->getMockBuilder(StatusRepository::class)
			->setConstructorArgs([$this->dbClient, $this->commentRepository, $this->likeRepository])
			->onlyMethods(['prepareData', 'buildHandledStatus', 'loadUsersInfo'])
			->getMock();

		$this->statusRepository->method('loadUsersInfo')
			->willReturn([1 => ['name' => 'Test User']]);
		$this->queryObject = new stdClass();
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('breeze_status', $this->statusRepository->getTableName());
	}

	public function testGetColumnId(): void
	{
		$this->assertEquals('id', $this->statusRepository->getColumnId());
	}

	public function testGetColumnPosterId(): void
	{
		$this->assertEquals('userId', $this->statusRepository->getColumnPosterId());
	}

	public function testGetColumns(): void
	{
		$this->assertEquals([
			'id',
			'wallId',
			'userId',
			'createdAt',
			'body',
			'likes',
		], $this->statusRepository->getColumns());
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function testInsert(): void
	{
		$statusEntity = new StatusEntity([
			StatusEntity::WALL_ID => 1,
			StatusEntity::USER_ID => 2,
			StatusEntity::BODY => 'Test status',
		]);
		$statusHandledEntities = [5 => new StatusHandledEntity([
			StatusEntity::ID => 5,
			StatusEntity::WALL_ID => 1,
			StatusEntity::USER_ID => 2,
			StatusEntity::BODY => 'Test status',
		])];

		$this->dbClient->expects($this->once())
			->method('insert');

		$this->dbClient->expects($this->once())
			->method('getInsertedId')
			->willReturn(5);

		$this->statusRepository->method('loadUsersInfo')
			->willReturn([2 => ['name' => 'Test User']]);

		$this->statusRepository->expects($this->once())
			->method('buildHandledStatus')
			->with([$statusEntity])
			->willReturn($statusHandledEntities);

		$result = $this->statusRepository->insert($statusEntity);

		$this->assertInstanceOf(StatusHandledEntity::class, $result);
		$this->assertEquals(5, $result->getId());
		$this->assertTrue($result->isNew());
	}

	public function testInsertThrowsExceptionWhenIdIsZero(): void
	{
		$statusEntity = new StatusEntity([
			StatusEntity::WALL_ID => 1,
			StatusEntity::USER_ID => 2,
			StatusEntity::BODY => 'Test status',
			StatusEntity::LIKES => 0,
		]);

		$this->dbClient->method('getInsertedId')->willReturn(0);

		$this->expectException(InvalidStatusException::class);
		$this->expectExceptionMessage('error_save_status');

		$this->statusRepository->insert($statusEntity);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function testGetById(): void
	{
		$mockData = [5 => new StatusHandledEntity([
			StatusEntity::ID => 5,
			StatusEntity::WALL_ID => 1,
			StatusEntity::USER_ID => 2,
			StatusEntity::BODY => 'Test status',
		])];

		$this->dbClient->expects($this->once())
			->method('query')
			->willReturn($this->queryObject);

		$this->statusRepository->method('loadUsersInfo')
			->willReturn([2 => ['name' => 'Test User']]);

		$this->statusRepository->expects($this->once())
			->method('prepareData')
			->with($this->queryObject)
			->willReturn($mockData);

		$this->statusRepository->expects($this->once())
			->method('buildHandledStatus')
			->with($mockData)
			->willReturn($mockData);

		$result = $this->statusRepository->getById(5);

		$this->assertEquals($mockData[5], $result);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function testGetIdThrowsExceptionWhenNotFound(): void
	{
		$this->dbClient->method('query')->willReturn(false);

		$this->expectException(DataNotFoundException::class);
		$this->expectExceptionMessage('error_no_status');

		$this->statusRepository->getById(5);
	}

	/**
	 * @throws DataNotFoundException
	 */
	#[DataProvider('deleteByIdProvider')]
	public function testDeleteById(int $statusId, bool $isExpectedException): void
	{
		$this->statusRepository = $this->getMockBuilder(StatusRepository::class)
			->setConstructorArgs([$this->dbClient, $this->commentRepository, $this->likeRepository])
			->onlyMethods(['delete'])
			->getMock();

		$this->commentRepository->expects($this->once())
			->method('deleteByStatusId')
			->with($statusId)
			->willReturn(true);

		$this->statusRepository->expects($this->once())
			->method('delete')
			->with([$statusId])
			->willReturn(!$isExpectedException);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
			$this->expectExceptionMessage('error_no_status');
		}

		$result = $this->statusRepository->deleteById($statusId);

		$this->assertTrue($result);
	}

	public static function deleteByIdProvider(): array
	{
		return [
			'deleteStatusValid' => [
				'statusId' => 5,
				'isExpectedException' => false,
			],
			'deleteStatusInvalid' => [
				'statusId' => 0,
				'isExpectedException' => true,
			],
		];
	}

	public function testGetByProfile(): void
	{
		$mockQueryObject = new stdClass();
		$mockData = [
			1 => new StatusHandledEntity([
				StatusEntity::ID => 1,
				StatusEntity::WALL_ID => 10,
				StatusEntity::USER_ID => 2,
				StatusEntity::BODY => 'Test status',
			]),
		];

		$this->dbClient->expects($this->once())
			->method('query')
			->willReturn($mockQueryObject);

		$this->commentRepository->expects($this->once())
			->method('getByProfile')
			->with([10])
			->willReturn([]);

		$this->statusRepository->method('loadUsersInfo')
			->willReturn([2 => ['name' => 'Test User']]);

		$this->statusRepository->expects($this->once())
			->method('prepareData')
			->with($mockQueryObject)
			->willReturn($mockData);

		$this->statusRepository->expects($this->once())
			->method('buildHandledStatus')
			->with($mockData)
			->willReturn(['data' => $mockData, 'total' => 1]);

		$result = $this->statusRepository->getByProfile([10]);

		$this->assertEquals(['data' => $mockData, 'total' => 1], $result);
	}
}
