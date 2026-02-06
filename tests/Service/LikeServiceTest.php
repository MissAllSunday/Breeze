<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Repository\LikeRepositoryInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class LikeServiceTest extends TestCase
{
	private MockObject|LikeRepositoryInterface $likeRepository;

	private LikeService $likeService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->likeRepository = $this->createMock(LikeRepositoryInterface::class);
		$this->likeService = new LikeService($this->likeRepository);
	}

	public function testCountOrphans(): void
	{
		$expectedCount = 5;

		$this->likeRepository->expects($this->once())
			->method('countOrphans')
			->willReturn($expectedCount);

		$result = $this->likeService->countOrphans();

		$this->assertEquals($expectedCount, $result);
	}

	public function testDeleteOrphans(): void
	{
		$this->likeRepository->expects($this->once())
			->method('deleteOrphans');

		$this->likeService->deleteOrphans();
	}
}
