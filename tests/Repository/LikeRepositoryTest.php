<?php

declare(strict_types=1);

namespace Breeze\Repository;

use Breeze\Database\ClientInterface;
use Breeze\Entity\LikeInfoEntity;
use Breeze\LikesEnum;
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
		$this->dbClient = $this->createMock(ClientInterface::class);
		$this->likeRepository = new LikeRepository($this->dbClient, null, null);
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('user_likes', $this->likeRepository->getTableName());
	}

	#[DataProvider('getByContentProvider')]
	public function testGetByContent(LikesEnum $type, array $contentIds, array $dbResult, int $expectedCount): void
	{
		$this->dbClient->expects($this->once())
			->method('query')
			->willReturn($dbResult);
		$this->dbClient->expects($this->exactly(2))
			->method('fetchAssoc')
			->willReturnOnConsecutiveCalls(
				reset($dbResult),
				null
			);
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
				[['content_id' => 1, 'like_time' => 5, 'id_member' => 1]],
				2,
			],
		];
	}
}
