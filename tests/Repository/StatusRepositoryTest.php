<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Model\StatusModelInterface;
use Breeze\Util\Validate\DataNotFoundException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class StatusRepositoryTest extends TestCase
{
	use ProphecyTrait;

	private array $stack;

	protected function setUp(): void
	{
		$this->stack = [
			'commentModel' => [
				'getByProfiles1' => [
					'usersIds' => [1,2,3],
					'data' => [
						1 => [
							1 => [
								'id' => 1,
								'statusId' => 1,
								'userId' => 1,
								'createdAt' => 581299200,
								'body' => 'comment body',
								'likes' => 0,
								'likesInfo' => [],
								'userData' => [
									'link' => 'Guest',
									'name' => 'Guest',
									'avatar' => ['href' => 'avatar_url/default.png'],
								],
							],
						], ],
				],
			],
			'commentRepository' => [
				'getByProfile1' =>
					[
						1 => [
							1 => [
								'id' => 1,
								'statusId' => 1,
								'userId' => 1,
								'createdAt' => 581299200,
								'body' => 'comment body',
								'likes' => 0,
							],
						], ],
			],
			'statusModel' => [
				'getStatusByProfile1' => [
					'usersIds' => [1,2,3],
					'data' => [
						1 => [
							1 => [
								'id' => 1,
								'statusId' => 1,
								'userId' => 1,
								'createdAt' => 581299200,
								'body' => 'status body body',
								'likes' => 0,
							],
						], ],
				],
				'getStatusByProfile2' => [
					'usersIds' => [],
					'data' => [],
				],
			],
			'likeRepository' => [
				'appendLikeData' => [
					'likesInfo' => [
						'contentId' => 1,
						'count' => 0,
						'alreadyLiked' => false,
						'type' => 'type',
						'canLike' => false,
						'additionalInfo' => '',
					],
				],
			], ];
	}

	/**
	 * @dataProvider saveProvider
	 * @throws InvalidStatusException
	 */
	public function testSave(array $dataToInsert, int $newId): void
	{
		$statusModel =  $this->prophesize(StatusModelInterface::class);
		$commentRepository = $this->prophesize(CommentRepositoryInterface::class);
		$likeRepository = $this->prophesize(LikeRepositoryInterface::class);

		$statusRepository = new StatusRepository(
			$statusModel->reveal(),
			$commentRepository->reveal(),
			$likeRepository->reveal()
		);

		$statusModel
			->insert($dataToInsert)
			->willReturn($newId);

		if ($newId === 0) {
			$this->expectException(InvalidStatusException::class);
		}

		$newStatusId = $statusRepository->save($dataToInsert);

		$this->assertEquals($newId, $newStatusId);
	}

	public function saveProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'dataToInsert' => [
					'wallId' => 1,
					'userId' => 1,
					'createdAt' => time(),
					'body' => 'body',
					'likes' => 0,
				],
				'newId' => 666,
			],
			'InvalidStatusException' => [
				'dataToInsert' => [
					'wallId' => 1,
					'userId' => 1,
					'createdAt' => time(),
					'body' => 'body',
					'likes' => 0,
				],
				'newId' => 0,
			],
		];
	}

	/**
	 * @dataProvider getByProfileProvider
	 * @throws DataNotFoundException
	 */
	public function testGetByProfile(
		array $userProfiles,
		array $statusModelWillReturn,
		array $commentsByProfileWillReturn,
		array $likesInfo,
		array $expectedResult,
	): void {
		$statusModel =  $this->createMock(StatusModelInterface::class);
		$commentRepository = $this->prophesize(CommentRepositoryInterface::class);
		$likeRepository = $this->createMock(LikeRepositoryInterface::class);

		$statusRepository = new StatusRepository(
			$statusModel,
			$commentRepository->reveal(),
			$likeRepository
		);

		$statusModel->method('getCount')->willReturn(1);
		$statusModel
			->method('getStatusByProfile')
			->willReturn($statusModelWillReturn);


		if (empty($statusModelWillReturn)) {
			$this->expectException(DataNotFoundException::class);
		}

		$commentRepository->getByProfile($userProfiles)->willReturn($commentsByProfileWillReturn);

		$statusWithLikes = array_map(function ($item) use ($likesInfo): array {
			$item['likesInfo'] = $likesInfo;

			return $item;
		}, $statusModelWillReturn['data']);

		$likeRepository->method('appendLikeData')->willReturn($statusWithLikes);
		$statusByProfile = $statusRepository->getByProfile($userProfiles);

		$this->assertEquals($expectedResult, $statusByProfile);
	}

	public function getByProfileProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'userProfiles' => [1],
				'statusModelWillReturn' => [
					'total' => 1,
					'usersIds' => [1,2,3],
					'data' => [
						1 => [
							'id' => 1,
							'wallId' => 1,
							'userId' => 1,
							'createdAt' => 581299200,
							'body' => 'status body',
							'likes' => 0,
						],
					],
				],
				'commentGetByProfile' => [
					1 => [
						1 => [
							'id' => 1,
							'statusId' => 1,
							'userId' => 1,
							'createdAt' => 581299200,
							'body' => 'comment body',
							'likes' => 0,
						],
					], ],
				'likesInfo' => [
					'contentId' => 1,
					'count' => 0,
					'alreadyLiked' => false,
					'type' => 'type',
					'canLike' => false,
					'additionalInfo' => '',
				],
				'expectedResult' => [
					'total' => 1,
					'data' => [
						1 => [
							'id' => 1,
							'wallId' => 1,
							'userId' => 1,
							'createdAt' => 581299200,
							'body' => 'status body',
							'likes' => 0,
							'comments' => [
								1 => [
									'id' => 1,
									'statusId' => 1,
									'userId' => 1,
									'createdAt' => 581299200,
									'body' => 'comment body',
									'likes' => 0,
								],
							],
							'likesInfo' => [
								'contentId' => 1,
								'count' => 0,
								'alreadyLiked' => false,
								'type' => 'type',
								'canLike' => false,
								'additionalInfo' => '',
							],
							'userData' => [
								'link' => 'Guest',
								'name' => 'Guest',
								'avatar' => ['href' => 'avatar_url/default.png'],
							],
						],],
				],
			],
		];
	}

	/**
	 * @dataProvider getByIdProvider
	 * @throws DataNotFoundException
	 */
	public function testGetById(
		int $statusId,
		array $statusModelWillReturn,
		array $commentsByProfileWillReturn,
		array $likesInfo,
		array $expectedResult
	): void {
		$statusModel =  $this->prophesize(StatusModelInterface::class);
		$commentRepository = $this->prophesize(CommentRepositoryInterface::class);
		$likeRepository = $this->createMock(LikeRepositoryInterface::class);

		$statusRepository = new StatusRepository(
			$statusModel->reveal(),
			$commentRepository->reveal(),
			$likeRepository
		);

		$statusModel
			->getById($statusId)
			->willReturn($statusModelWillReturn);

		if (empty($statusModelWillReturn)) {
			$this->expectException(DataNotFoundException::class);
		}

		$commentRepository->getByStatus([$statusId])->willReturn($commentsByProfileWillReturn);

		$statusWithLikes = array_map(function ($item) use ($likesInfo): array {
			$item['likesInfo'] = $likesInfo;

			return $item;
		}, $statusModelWillReturn['data']);

		$likeRepository->method('appendLikeData')->willReturn($statusWithLikes);
		$statusById = $statusRepository->getById($statusId);

		$this->assertEquals($expectedResult, $statusById);
	}

	public function getByIdProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'statusId' => 1,
				'statusModelWillReturn' => [
					'usersIds' => [1,2,3],
					'data' => [
						1 => [
							'id' => 1,
							'wallId' => 1,
							'userId' => 1,
							'createdAt' => 581299200,
							'body' => 'status body',
							'likes' => 0,
						],
					],
				],
				'commentGetByProfile' => [
					1 => [
						1 => [
							'id' => 1,
							'statusId' => 1,
							'userId' => 1,
							'createdAt' => 581299200,
							'body' => 'comment body',
							'likes' => 0,
						],
					], ],
				'likesInfo' => [
					'contentId' => 1,
					'count' => 0,
					'alreadyLiked' => false,
					'type' => 'type',
					'canLike' => false,
					'additionalInfo' => '',
				],
				'expectedResult' => [
					1 => [
						'id' => 1,
						'wallId' => 1,
						'userId' => 1,
						'createdAt' => 581299200,
						'body' => 'status body',
						'likes' => 0,
						'comments' => [
							1 => [
								'id' => 1,
								'statusId' => 1,
								'userId' => 1,
								'createdAt' => 581299200,
								'body' => 'comment body',
								'likes' => 0,
							],
						],
						'likesInfo' => [
							'contentId' => 1,
							'count' => 0,
							'alreadyLiked' => false,
							'type' => 'type',
							'canLike' => false,
							'additionalInfo' => '',
						],
						'userData' => [
							'link' => 'Guest',
							'name' => 'Guest',
							'avatar' => ['href' => 'avatar_url/default.png'],
						],
					],],
			],
		];
	}

	/**
	 * @dataProvider deleteByIdProvider
	 * @throws DataNotFoundException
	 */
	public function testDeleteById(
		int $statusId,
		array $statusModelWillReturn,
		bool $deleteByStatusIdWillReturn,
		bool $expectedResult
	): void {
		$statusModel = $this->prophesize(StatusModelInterface::class);
		$commentRepository = $this->createMock(CommentRepositoryInterface::class);
		$likeRepository = $this->createMock(LikeRepositoryInterface::class);

		$statusModel->getById($statusId)->willReturn($statusModelWillReturn);
		$commentRepository->method('getByStatus')->willReturn([]);

		$likeRepository->method('appendLikeData')->willReturn($statusModelWillReturn['data']);
		$commentRepository->method('deleteByStatusId')
			->willReturn(true);

		$statusModel->delete([$statusId])->willReturn($expectedResult);

		$statusRepository = new StatusRepository(
			$statusModel->reveal(),
			$commentRepository,
			$likeRepository
		);

		if (!$expectedResult) {
			$this->expectException(DataNotFoundException::class);
		}

		$deleteById = $statusRepository->deleteById($statusId);

		$this->assertEquals($expectedResult, $deleteById);
	}

	public function deleteByIdProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'statusId' => 1,
				'statusModelWillReturn' => [
					'usersIds' => [1],
					'data' => [
						1 => [
							'id' => 1,
							'wallId' => 1,
							'userId' => 1,
							'createdAt' => 581299200,
							'body' => 'status body',
							'likes' => 0,
							'userData' => [
								'link' => 'Guest',
								'name' => 'Guest',
								'avatar' => ['href' => 'avatar_url/default.png'],
							],
						],
					],
				],
				'deleteByStatusIdWillReturn' => true,
				'expectedResult' => true,
			],
			'could not be deleted' => [
				'statusId' => 1,
				'statusModelWillReturn' => [
					'usersIds' => [1],
					'data' => [
						1 => [
							'id' => 1,
							'wallId' => 1,
							'userId' => 1,
							'createdAt' => 581299200,
							'body' => 'status body',
							'likes' => 0,
							'userData' => [
								'link' => 'Guest',
								'name' => 'Guest',
								'avatar' => ['href' => 'avatar_url/default.png'],
							],
						],
					],
				],
				'deleteByStatusIdWillReturn' => true,
				'expectedResult' => false,
			],
		];
	}
}
