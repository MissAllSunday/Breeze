<?php

declare(strict_types=1);

namespace Breeze\Repository;

use Breeze\Database\ClientInterface;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\LikeInfoEntity;
use Breeze\Entity\SharedEntity;
use Breeze\Entity\StatusEntity;
use Breeze\LikesEnum;
use Breeze\Util\Validate\DataNotFoundException;
use DateMalformedStringException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

#[AllowMockObjectsWithoutExpectations]
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
			->onlyMethods(['prepareData', 'loadUsersInfo'])
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
		$this->assertEquals('user_id', $this->statusRepository->getColumnPosterId());
	}

	public function testGetColumns(): void
	{
		$this->assertEquals([
			'id',
			'wall_id',
			'user_id',
			'created_at',
			'body',
			'likes',
		], $this->statusRepository->getColumns());
	}

	/**
	 * @throws InvalidStatusException|DateMalformedStringException
	 */
	public function testInsert(): void
	{
		$statusEntity = StatusEntity::from([
			StatusEntity::ID => 5,
			StatusEntity::WALL_ID => 1,
			StatusEntity::USER_ID => 2,
			StatusEntity::BODY => 'Test status',
			SharedEntity::CREATED_AT => time(),
		]);

		$this->dbClient->expects($this->once())
			->method('insert');

		$this->dbClient->expects($this->once())
			->method('getInsertedId')
			->willReturn(5);

		$this->statusRepository->method('loadUsersInfo')
			->willReturn([2 => ['name' => 'Test User']]);

		$this->likeRepository->expects($this->once())
			->method('getByContent')
			->with(LikesEnum::Status, [5])
			->willReturn([
				5 => LikeInfoEntity::from([
					LikeEntity::ID => 5,
					LikeInfoEntity::LIKES => [],
				]),
			]);

		$result = $this->statusRepository->insert($statusEntity);

		$this->assertInstanceOf(StatusEntity::class, $result[0]);
		$this->assertEquals(5, $result[0]->getId());
		$this->assertTrue($result[0]->isNew());
	}

	public function testInsertThrowsExceptionWhenIdIsZero(): void
	{
		$statusEntity = StatusEntity::from([
			StatusEntity::WALL_ID => 1,
			StatusEntity::USER_ID => 2,
			StatusEntity::BODY => 'Test status',
			StatusEntity::LIKES => 0,
			SharedEntity::CREATED_AT => time(),
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
		$mockData = [5 => StatusEntity::from([
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
			1 => StatusEntity::from([
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

		$result = $this->statusRepository->getByProfile([10]);

		$this->assertEquals($mockData, $result);
	}

	#[DataProvider('getByProvider')]
	public function testGetBy(string $columnName, array $data, int $maxIndex, array $expected): void
	{
		$this->dbClient->expects($this->once())
			->method('query')
			->willReturn($this->queryObject);

		$this->commentRepository->expects($this->once())
			->method('getByProfile')
			->with($data)
			->willReturn([]);

		$this->statusRepository->expects($this->once())
			->method('prepareData')
			->with($this->queryObject, [])
			->willReturn($expected);

		$result = $this->statusRepository->getBy($columnName, $data, $maxIndex);

		$this->assertEquals($expected, $result);
	}

	public static function getByProvider(): array
	{
		return [
			'valid column with data' => [
				'columnName' => StatusEntity::WALL_ID,
				'data' => [1, 2, 3],
				'maxIndex' => 10,
				'expected' => [
					1 => StatusEntity::from([
						StatusEntity::ID => 1,
						StatusEntity::WALL_ID => 1,
						StatusEntity::USER_ID => 2,
						StatusEntity::BODY => 'Test status',
					]),
				],
			],
			'empty data array' => [
				'columnName' => StatusEntity::USER_ID,
				'data' => [],
				'maxIndex' => 15,
				'expected' => [],
			],
		];
	}

	public function testGetByWithInvalidColumn(): void
	{
		$result = $this->statusRepository->getBy('invalid_column', [1, 2], 10);

		$this->assertEquals([], $result);
	}

	public function testGetByCallsCorrectQueryParams(): void
	{
		$columnName = StatusEntity::WALL_ID;
		$data = [1, 2, 3];
		$maxIndex = 20;

		$expectedParams = [
			'columns' => 'parent.id, parent.wall_id, parent.user_id, parent.created_at, parent.body, parent.likes',
			'from' => 'breeze_status AS parent',
			'columnName' => $columnName,
			'limit' => $maxIndex,
			'ids' => $data,
			'tableName' => 'breeze_status',
		];

		$this->dbClient->expects($this->once())
			->method('query')
			->with(
				$this->stringContains('SELECT {raw:columns}'),
				$expectedParams
			)
			->willReturn($this->queryObject);

		$this->commentRepository->method('getByProfile')->willReturn([]);
		$this->statusRepository->method('prepareData')->willReturn([]);

		$this->statusRepository->getBy($columnName, $data, $maxIndex);
	}

	// Cursor-based pagination tests

	public function testEncodeCursor(): void
	{
		$id = 123;
		$createdAt = 1234567890;

		$cursor = $this->statusRepository->encodeCursor($id, $createdAt);

		$this->assertIsString($cursor);
		$this->assertNotEmpty($cursor);

		// Verify it's valid base64
		$decoded = base64_decode($cursor, true);
		$this->assertNotFalse($decoded);

		// Verify the content
		$data = json_decode($decoded, true);
		$this->assertEquals($id, $data['id']);
		$this->assertEquals($createdAt, $data['created_at']);
	}

	public function testDecodeCursor(): void
	{
		$id = 456;
		$createdAt = 9876543210;

		$cursor = $this->statusRepository->encodeCursor($id, $createdAt);
		$decoded = $this->statusRepository->decodeCursor($cursor);

		$this->assertIsArray($decoded);
		$this->assertEquals($id, $decoded['id']);
		$this->assertEquals($createdAt, $decoded['created_at']);
	}

	public function testDecodeCursorWithInvalidBase64(): void
	{
		$result = $this->statusRepository->decodeCursor('invalid!!!base64');

		$this->assertNull($result);
	}

	public function testDecodeCursorWithInvalidJson(): void
	{
		$invalidCursor = base64_encode('not json');
		$result = $this->statusRepository->decodeCursor($invalidCursor);

		$this->assertNull($result);
	}

	public function testDecodeCursorWithMissingFields(): void
	{
		$invalidData = base64_encode(json_encode(['id' => 123])); // missing created_at
		$result = $this->statusRepository->decodeCursor($invalidData);

		$this->assertNull($result);
	}

	/**
	 * @throws DateMalformedStringException
	 */
	public function testGetNextCursor(): void
	{
		$statuses = [
			StatusEntity::from([
				StatusEntity::ID => 1,
				StatusEntity::WALL_ID => 1,
				StatusEntity::USER_ID => 1,
				StatusEntity::BODY => 'First',
				SharedEntity::CREATED_AT => 1000,
			]),
			StatusEntity::from([
				StatusEntity::ID => 2,
				StatusEntity::WALL_ID => 1,
				StatusEntity::USER_ID => 1,
				StatusEntity::BODY => 'Second',
				SharedEntity::CREATED_AT => 2000,
			]),
			StatusEntity::from([
				StatusEntity::ID => 3,
				StatusEntity::WALL_ID => 1,
				StatusEntity::USER_ID => 1,
				StatusEntity::BODY => 'Third',
				SharedEntity::CREATED_AT => 3000,
			]),
		];

		$cursor = $this->statusRepository->getNextCursor($statuses);

		$this->assertNotNull($cursor);

		$decoded = $this->statusRepository->decodeCursor($cursor);
		$this->assertEquals(3, $decoded['id']);
		$this->assertEquals(3000, $decoded['created_at']);
	}

	public function testGetNextCursorWithEmptyArray(): void
	{
		$cursor = $this->statusRepository->getNextCursor([]);

		$this->assertNull($cursor);
	}

	public function testGetByWithCursor(): void
	{
		$columnName = StatusEntity::WALL_ID;
		$data = [1, 2, 3];
		$maxIndex = 10;
		$cursor = $this->statusRepository->encodeCursor(100, 1234567890);

		$this->dbClient->expects($this->once())
			->method('query')
			->with(
				$this->stringContains('ORDER BY parent.created_at DESC, parent.id DESC'),
				$this->callback(function ($params) use ($columnName, $data, $maxIndex) {
					return $params['columnName'] === $columnName
						&& $params['ids'] === $data
						&& $params['limit'] === $maxIndex
						&& isset($params['cursor_id'])
						&& isset($params['cursor_created_at'])
						&& $params['cursor_id'] === 100
						&& $params['cursor_created_at'] === 1234567890;
				})
			)
			->willReturn($this->queryObject);

		$this->commentRepository->method('getByProfile')->willReturn([]);
		$this->statusRepository->method('prepareData')->willReturn([]);

		$this->statusRepository->getBy($columnName, $data, $maxIndex, $cursor);
	}

	public function testGetByProfileWithCursor(): void
	{
		$userProfiles = [1];
		$maxIndex = 10;
		$cursor = $this->statusRepository->encodeCursor(50, 9999999);

		$this->dbClient->expects($this->once())
			->method('query')
			->with(
				$this->stringContains('ORDER BY parent.created_at DESC'),
				$this->anything()
			)
			->willReturn($this->queryObject);

		$this->commentRepository->method('getByProfile')->willReturn([]);
		$this->statusRepository->method('prepareData')->willReturn([]);

		$result = $this->statusRepository->getByProfile($userProfiles, $maxIndex, $cursor);

		$this->assertIsArray($result);
	}
}
