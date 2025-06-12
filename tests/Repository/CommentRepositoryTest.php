<?php

declare(strict_types=1);

namespace Breeze\Repository;

use Breeze\Database\ClientInterface;
use Breeze\Entity\CommentEntity;
use Breeze\Entity\CommentHandledEntity;
use Breeze\Util\Validate\DataNotFoundException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CommentRepositoryTest extends TestCase
{
	private MockObject|ClientInterface $dbClient;

	private MockObject|LikeRepositoryInterface $likeRepository;

	private MockObject|CommentRepository $commentRepository;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->dbClient = $this->createMock(ClientInterface::class);
		$this->likeRepository = $this->createMock(LikeRepositoryInterface::class);
		$this->commentRepository = $this->getMockBuilder(CommentRepository::class)
			->setConstructorArgs([$this->dbClient, $this->likeRepository])
			->onlyMethods(['prepareData', 'buildHandledComments', 'loadUsersInfo'])
			->getMock();
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('breeze_comments', $this->commentRepository->getTableName());
	}

	public function testGetColumnId(): void
	{
		$this->assertEquals(CommentEntity::ID, $this->commentRepository->getColumnId());
	}

	public function testGetColumnPosterId(): void
	{
		$this->assertEquals('userId', $this->commentRepository->getColumnPosterId());
	}

	public function testGetColumns(): void
	{
		$this->assertEquals(CommentEntity::getColumns(), $this->commentRepository->getColumns());
	}

	/**
	 * @throws InvalidCommentException
	 */
	public function testInsert(): void
	{
		$commentEntity = new CommentEntity([
			CommentEntity::STATUS_ID => 1,
			CommentEntity::USER_ID => 2,
			CommentEntity::BODY => 'Test comment',
			CommentEntity::LIKES => 0,
		]);
		$commentHandledEntities = [5 => new CommentHandledEntity([
			CommentEntity::ID => 5,
			CommentEntity::STATUS_ID => 1,
			CommentEntity::USER_ID => 2,
			CommentEntity::BODY => 'Test comment',
		])];

		$this->dbClient->expects($this->once())
			->method('insert');

		$this->dbClient->expects($this->once())
			->method('getInsertedId')
			->willReturn(5);

		$this->commentRepository->method('loadUsersInfo')
			->willReturn([2 => ['name' => 'Test User']]);

		$this->commentRepository->expects($this->once())
			->method('buildHandledComments')
			->with([$commentEntity])
			->willReturn($commentHandledEntities);

		$result = $this->commentRepository->insert($commentEntity);

		$this->assertInstanceOf(CommentHandledEntity::class, $result);
		$this->assertEquals(5, $result->getId());
	}

	public function testInsertThrowsExceptionWhenIdIsZero(): void
	{
		$commentEntity = new CommentEntity([
			CommentEntity::STATUS_ID => 1,
			CommentEntity::USER_ID => 2,
			CommentEntity::BODY => 'Test comment',
			CommentEntity::LIKES => 0,
		]);

		$this->dbClient->method('getInsertedId')->willReturn(0);

		$this->expectException(InvalidCommentException::class);
		$this->expectExceptionMessage('error_save_comment');

		$this->commentRepository->insert($commentEntity);
	}

	public function testGetById(): void
	{
		$mockResult = 'mock_result';
		$mockData = [5 => new CommentHandledEntity([
			CommentEntity::ID => 5,
			CommentEntity::STATUS_ID => 1,
			CommentEntity::USER_ID => 2,
			CommentEntity::BODY => 'Test comment',
		])];

		$this->dbClient->expects($this->once())
			->method('query')
			->willReturn($mockResult);

		$this->commentRepository = $this->getMockBuilder(CommentRepository::class)
			->setConstructorArgs([$this->dbClient, $this->likeRepository])
			->onlyMethods(['prepareData', 'buildHandledComments'])
			->getMock();

		$this->commentRepository->expects($this->once())
			->method('prepareData')
			->with($mockResult)
			->willReturn($mockData);

		$this->commentRepository->expects($this->once())
			->method('buildHandledComments')
			->with($mockData)
			->willReturn($mockData);

		$result = $this->commentRepository->getById(5);

		$this->assertEquals($mockData, $result);
	}

	public function testDeleteById(): void
	{
		$this->commentRepository = $this->getMockBuilder(CommentRepository::class)
			->setConstructorArgs([$this->dbClient, $this->likeRepository])
			->onlyMethods(['delete', 'setCache'])
			->getMock();

		$this->commentRepository->expects($this->once())
			->method('delete')
			->with([5])
			->willReturn(true);

		$this->commentRepository->expects($this->once())
			->method('setCache')
			->with(CommentRepository::class . '::getById5', null);

		$result = $this->commentRepository->deleteById(5);

		$this->assertTrue($result);
	}

	public function testDeleteByIdThrowsExceptionWhenDeleteFails(): void
	{
		$this->commentRepository = $this->getMockBuilder(CommentRepository::class)
			->setConstructorArgs([$this->dbClient, $this->likeRepository])
			->onlyMethods(['delete'])
			->getMock();

		$this->commentRepository->expects($this->once())
			->method('delete')
			->willReturn(false);

		$this->expectException(DataNotFoundException::class);
		$this->expectExceptionMessage('error_no_comment');

		$this->commentRepository->deleteById(5);
	}

	public function testDeleteByStatusId(): void
	{
		$this->dbClient->expects($this->once())
			->method('delete')
			->with(
				CommentEntity::TABLE,
				'WHERE ' . CommentEntity::STATUS_ID . ' ={int:statusId}',
				['statusId' => 10]
			)
			->willReturn(true);

		$result = $this->commentRepository->deleteByStatusId(10);

		$this->assertTrue($result);
	}
}
