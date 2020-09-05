<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Model\CommentModel;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CommentRepositoryTest extends TestCase
{
	/**
	 * @var MockBuilder|CommentModel
	 */
	private $commentModel;

	private CommentRepository $commentRepository;

	public function setUp(): void
	{
		$this->commentModel = $this->getMockInstance(CommentModel::class);
		
		$this->commentRepository = new CommentRepository($this->commentModel);
	}

	/**
	 * @dataProvider saveProvider
	 * @throws InvalidCommentException
	 */
	public function testSave(array $dataToInsert, int $newId): void
	{
		$this->commentModel
			->expects($this->once())
			->method('insert')
			->with($dataToInsert)
			->willReturn($newId);

		if (0 === $newId)
			$this->expectException(InvalidCommentException::class);

		$newCommentId = $this->commentRepository->save($dataToInsert);

		$this->assertEquals($newId, $newCommentId);

	}

	public function saveProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'dataToInsert' => [
					'comments_time' => time(),
					'likes' => 0,
				],
				'newId' => 666,
			],
			'InvalidCommentException' => [
				'dataToInsert' => [
					'comments_time' => time(),
					'likes' => 0,
				],
				'newId' => 0,
			],
		];
	}

	/**
	 * @dataProvider getByProfileProvider
	 */
	public function testGetByProfile(int $profileOwnerId, array $commentsByProfileWillReturn): void
	{
		if (1 !== $profileOwnerId)
		{
			$this->commentModel
				->expects($this->once())
				->method('getByProfiles')
				->with([$profileOwnerId])
				->willReturn($commentsByProfileWillReturn);
		}

		$commentsByProfile = $this->commentRepository->getByProfile($profileOwnerId);

		$this->assertEquals($commentsByProfileWillReturn, $commentsByProfile);
	}

	public function getByProfileProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'profileOwnerId' => 1,
				'commentsByProfileWillReturn' => [
					'some data'
				],
			],
			'no data' => [
				'profileOwnerId' => 2,
				'commentsByProfileWillReturn' => [],
			],
			'data from query' => [
				'profileOwnerId' => 3,
				'commentsByProfileWillReturn' => [
					'some data'
				],
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
