<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Model\StatusModelInterface;
use Breeze\Util\Validate\DataNotFoundException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StatusRepositoryTest extends TestCase
{
	protected array $stack;

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
								'createdAt' => 'Today',
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
								'createdAt' => 'Today',
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
								'createdAt' => 'Today',
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

	#[DataProvider('saveProvider')]
	public function testSave(array $dataToInsert, int $newId): void
	{
		$statusModel =  $this->createMock(StatusModelInterface::class);
		$commentRepository = $this->createMock(CommentRepositoryInterface::class);
		$likeRepository = $this->createMock(LikeRepositoryInterface::class);

		$statusRepository = new StatusRepository(
			$statusModel,
			$commentRepository,
			$likeRepository
		);

		$statusModel
			->method('insert')
			->willReturn($newId);

		if ($newId === 0) {
			$this->expectException(InvalidStatusException::class);
		}

		$newStatusId = $statusRepository->save($dataToInsert);

		$this->assertEquals($newId, $newStatusId);
	}

	public static function saveProvider(): array
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

	#[DataProvider('getByProfileProvider')]
	public function testGetByProfile(
		array $userProfiles,
		array $statusModelWillReturn,
		array $commentsByProfileWillReturn,
		array $likesInfo,
		array $expectedResult,
	): void {
		$statusModel =  $this->createMock(StatusModelInterface::class);
		$commentRepository = $this->createMock(CommentRepositoryInterface::class);
		$likeRepository = $this->createMock(LikeRepositoryInterface::class);

		$statusRepository = new StatusRepository(
			$statusModel,
			$commentRepository,
			$likeRepository
		);

		$statusModel->method('getCount')->willReturn(1);
		$statusModel
			->method('getStatusByProfile')
			->willReturn($statusModelWillReturn);


		if (empty($statusModelWillReturn)) {
			$this->expectException(DataNotFoundException::class);
		}

		$commentRepository->method('getByProfile')->willReturn($commentsByProfileWillReturn);

		$statusWithLikes = array_map(function ($item) use ($likesInfo): array {
			$item['likesInfo'] = $likesInfo;

			return $item;
		}, $statusModelWillReturn['data']);

		$likeRepository->method('appendLikeData')->willReturn($statusWithLikes);
		$statusByProfile = $statusRepository->getByProfile($userProfiles);

		$this->assertEquals($expectedResult, $statusByProfile);
	}

	public static function getByProfileProvider(): array
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
							'createdAt' => 'Today',
							'body' => 'status body',
							'likes' => 0,
						],
					],
				],
				'commentsByProfileWillReturn' => [
					1 => [
						1 => [
							'id' => 1,
							'statusId' => 1,
							'userId' => 1,
							'createdAt' => 'Today',
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
							'createdAt' => 'Today',
							'body' => 'status body',
							'likes' => 0,
							'comments' => [
								1 => [
									'id' => 1,
									'statusId' => 1,
									'userId' => 1,
									'createdAt' => 'Today',
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

	#[DataProvider('getByIdProvider')]
	public function testGetById(
		int $statusId,
		array $statusModelWillReturn,
		array $commentsByProfileWillReturn,
		array $likesInfo,
		array $expectedResult
	): void {
		$statusModel =  $this->createMock(StatusModelInterface::class);
		$commentRepository = $this->createMock(CommentRepositoryInterface::class);
		$likeRepository = $this->createMock(LikeRepositoryInterface::class);

		$statusRepository = new StatusRepository(
			$statusModel,
			$commentRepository,
			$likeRepository
		);

		$statusModel
			->method('getById')
			->willReturn($statusModelWillReturn);

		if (empty($statusModelWillReturn)) {
			$this->expectException(DataNotFoundException::class);
		}

		$commentRepository->method('getByStatus')->willReturn($commentsByProfileWillReturn);

		$statusWithLikes = array_map(function ($item) use ($likesInfo): array {
			$item['likesInfo'] = $likesInfo;

			return $item;
		}, $statusModelWillReturn['data']);

		$likeRepository->method('appendLikeData')->willReturn($statusWithLikes);
		$statusById = $statusRepository->getById($statusId);

		$this->assertEquals($expectedResult, $statusById);
	}

	public static function getByIdProvider(): array
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
							'createdAt' => 'Today',
							'body' => 'status body',
							'likes' => 0,
						],
					],
				],
				'commentsByProfileWillReturn' => [
					1 => [
						1 => [
							'id' => 1,
							'statusId' => 1,
							'userId' => 1,
							'createdAt' => 'Today',
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
						'createdAt' => 'Today',
						'body' => 'status body',
						'likes' => 0,
						'comments' => [
							1 => [
								'id' => 1,
								'statusId' => 1,
								'userId' => 1,
								'createdAt' => 'Today',
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

	#[DataProvider('deleteByIdProvider')]
	public function testDeleteById(
		int $statusId,
		array $statusModelWillReturn,
		bool $deleteByStatusIdWillReturn,
		bool $expectedResult
	): void {
		$statusModel = $this->createMock(StatusModelInterface::class);
		$commentRepository = $this->createMock(CommentRepositoryInterface::class);
		$likeRepository = $this->createMock(LikeRepositoryInterface::class);

		$statusModel->method('getById')->willReturn($statusModelWillReturn);
		$commentRepository->method('getByStatus')->willReturn([]);

		$likeRepository->method('appendLikeData')->willReturn($statusModelWillReturn['data']);
		$commentRepository->method('deleteByStatusId')
			->willReturn(true);

		$statusModel->method('delete')->willReturn($expectedResult);

		$statusRepository = new StatusRepository(
			$statusModel,
			$commentRepository,
			$likeRepository
		);

		if (!$expectedResult) {
			$this->expectException(DataNotFoundException::class);
		}

		$deleteById = $statusRepository->deleteById($statusId);

		$this->assertEquals($expectedResult, $deleteById);
	}

	public static function deleteByIdProvider(): array
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
							'createdAt' => 'Today',
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
							'createdAt' => 'Today',
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
