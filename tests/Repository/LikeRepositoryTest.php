<?php

declare(strict_types=1);

namespace Breeze\Repository;

use Breeze\Database\ClientInterface;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\LikeInfoEntity;
use Breeze\Enums\LikesEnum;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class LikeRepositoryTest extends TestCase
{
	private MockObject|ClientInterface $dbClient;

	private LikeRepository $likeRepository;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		BaseRepository::resetUsersInfoMemo();
		$this->dbClient = $this->createMock(ClientInterface::class);
		$this->likeRepository = new LikeRepository($this->dbClient);
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('user_likes', $this->likeRepository->getTableName());
	}

	public function testGetColumnId(): void
	{
		$this->assertEquals('content_id', $this->likeRepository->getColumnId());
	}

	public function testGetColumnPosterId(): void
	{
		$this->assertEquals('id_member', $this->likeRepository->getColumnPosterId());
	}

	public function testGetColumns(): void
	{
		$expected = [
			'id_member',
			'content_type',
			'content_id',
			'like_time',
		];
		$this->assertEquals($expected, $this->likeRepository->getColumns());
	}

	#[DataProvider('getByContentProvider')]
	public function testGetByContent(LikesEnum $type, array $contentIds, array $dbResult, int $expectedCount): void
	{
		$this->dbClient->expects($this->once())
			->method('query')
			->willReturn($dbResult);

		if (empty($dbResult)) {
			// For empty results, fetchAssoc should be called once and return null
			$this->dbClient->expects($this->once())
				->method('fetchAssoc')
				->with($dbResult)
				->willReturn(null);
		} else {
			// For non-empty results, fetchAssoc should be called multiple times
			$this->dbClient->expects($this->exactly(2))
				->method('fetchAssoc')
				->willReturnOnConsecutiveCalls(
					reset($dbResult),
					null
				);
		}

		$this->dbClient->expects($this->once())
			->method('freeResult');

		$result = $this->likeRepository->getByContent($type, $contentIds);

		$this->assertCount($expectedCount, $result);
		if ($expectedCount > 0) {
			$this->assertInstanceOf(LikeInfoEntity::class, reset($result));
		}
	}

	public static function getByContentProvider(): array
	{
		return [
			'status likes found' => [
				LikesEnum::Status,
				[1, 2],
				[['content_id' => 1, 'content_type' => LikesEnum::Status->value, 'like_time' => 5, 'id_member' => 1]],
				2,
			],
			'comment likes found' => [
				LikesEnum::Comments,
				[3, 4],
				[['content_id' => 3, 'content_type' => LikesEnum::Comments->value, 'like_time' => 10, 'id_member' => 2]],
				2,
			],
			'no likes found' => [
				LikesEnum::Status,
				[5, 6],
				[],
				2,
			],
		];
	}

	/**
	 * @throws Exception
	 */
	public function testGetByContentLoadsLikerMembersOnceForTheWholeBatch(): void
	{
		$type = LikesEnum::Status;
		$contentIds = [1, 2, 3];

		$rows = [
			['content_id' => 1, 'content_type' => $type->value, 'like_time' => 5, 'id_member' => 1],
			['content_id' => 2, 'content_type' => $type->value, 'like_time' => 6, 'id_member' => 3],
			['content_id' => 3, 'content_type' => $type->value, 'like_time' => 7, 'id_member' => 4],
		];

		$likeRepository = $this->getMockBuilder(LikeRepository::class)
			->setConstructorArgs([$this->dbClient])
			->onlyMethods(['getUsersToLoad'])
			->getMock();

		$likeRepository->expects($this->once())
			->method('getUsersToLoad')
			->with([1, 3, 4])
			->willReturn([1, 3, 4]);

		$this->dbClient->expects($this->once())
			->method('query')
			->willReturn('result');

		$this->dbClient->method('fetchAssoc')
			->willReturnOnConsecutiveCalls($rows[0], $rows[1], $rows[2], null);

		$this->dbClient->expects($this->once())
			->method('freeResult');

		$result = $likeRepository->getByContent($type, $contentIds);

		$this->assertCount(3, $result);
	}

	public function testIsContentAlreadyLikedTrue(): void
	{
		$this->dbClient->expects($this->once())
			->method('query')
			->willReturn('result');
		$this->dbClient->expects($this->once())
			->method('numRows')
			->with('result')
			->willReturn(1);
		$this->dbClient->expects($this->once())
			->method('freeResult');

		$likeEntity = LikeEntity::from();
		$likeEntity->setContentId(1);
		$likeEntity->setIdMember(2);
		$likeEntity->setContentType(LikesEnum::Status);

		$result = $this->likeRepository->isContentAlreadyLiked($likeEntity);

		$this->assertTrue($result);
	}
}
