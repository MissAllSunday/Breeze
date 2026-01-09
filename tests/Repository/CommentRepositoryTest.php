<?php

declare(strict_types=1);

namespace Breeze\Repository;

use Breeze\Database\ClientInterface;
use Breeze\Entity\CommentEntity;
use Breeze\Entity\LikeInfoEntity;
use Breeze\Fixtures\CommentFixtures;
use Breeze\Fixtures\LikeInfoFixtures;
use Breeze\LikesEnum;
use Breeze\Util\Validate\DataNotFoundException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class CommentRepositoryTest extends TestCase
{
	private MockObject|ClientInterface $dbClient;

	private MockObject|LikeRepositoryInterface $likeRepository;

	private MockObject|CommentRepository $commentRepository;

	private \stdClass $queryObject;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->dbClient = $this->createMock(ClientInterface::class);
		$this->likeRepository = $this->createMock(LikeRepositoryInterface::class);
		$this->commentRepository = $this->getMockBuilder(CommentRepository::class)
			->setConstructorArgs([$this->dbClient, $this->likeRepository])
			->onlyMethods(['prepareData', 'loadUsersInfo'])
			->getMock();
		$this->queryObject = new \stdClass();
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
		$this->assertEquals('user_id', $this->commentRepository->getColumnPosterId());
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
		$commentEntity = CommentEntity::from(CommentFixtures::forInsertion());

		$this->dbClient->expects($this->once())
			->method('insert');

		$this->dbClient->expects($this->once())
			->method('getInsertedId')
			->willReturn(5);

		$this->commentRepository->method('loadUsersInfo')
			->willReturn([2 => ['name' => 'Test User']]);

		$this->likeRepository->expects($this->once())
			->method('getByContent')
			->with(LikesEnum::Comments, [5])
			->willReturn([
				5 => LikeInfoEntity::from(LikeInfoFixtures::basic()),
			]);

		$result = $this->commentRepository->insert($commentEntity);

		$this->assertInstanceOf(CommentEntity::class, $result[0]);
		$this->assertEquals(5, $result[0]->getId());
	}

	public function testInsertThrowsExceptionWhenIdIsZero(): void
	{
		$commentEntity = CommentEntity::from(CommentFixtures::forInsertion());

		$this->dbClient->method('getInsertedId')->willReturn(0);

		$this->expectException(InvalidCommentException::class);
		$this->expectExceptionMessage('error_save_comment');

		$this->commentRepository->insert($commentEntity);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function testGetById(): void
	{
		$commentData = CommentFixtures::withCustomData([CommentEntity::ID => 5]);
		$commentHandledEntities = [5 => CommentEntity::from($commentData)];

		$this->dbClient->expects($this->once())
			->method('query')
			->willReturn($this->queryObject);

		$this->commentRepository->expects($this->once())
			->method('prepareData')
			->with($this->queryObject)
			->willReturn($commentHandledEntities);

		$result = $this->commentRepository->getById(5);

		$this->assertEquals($commentHandledEntities[5], $result);
	}

	public function testGetIdThrowsExceptionWhenNotFound(): void
	{
		$this->dbClient->method('query')->willReturn(false);

		$this->expectException(DataNotFoundException::class);
		$this->expectExceptionMessage('error_no_comment');

		$this->commentRepository->getById(5);
	}

	/**
	 * @throws DataNotFoundException
	 */
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

	public function testGetByStatusId(): void
	{
		$comments = CommentFixtures::multipleComments();
		$commentEntities = [];
		foreach ($comments as $comment) {
			$commentEntities[] = CommentEntity::from($comment);
		}

		$this->dbClient->expects($this->once())
			->method('query')
			->willReturn($this->queryObject);

		$this->commentRepository->expects($this->once())
			->method('prepareData')
			->with($this->queryObject)
			->willReturn($commentEntities);

		$result = $this->commentRepository->getByStatus([1]);

		$this->assertIsArray($result);
		$this->assertCount(3, $result);
	}
}
