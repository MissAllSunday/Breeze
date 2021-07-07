<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Repository\LikeRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LikeServiceTest extends TestCase
{
	/**
	 * @var MockObject|LikeRepositoryInterface
	 */
	private $likeRepository;

	private LikeService $likeService;

	public function setUp(): void
	{
		$this->likeRepository = $this->createMock(LikeRepositoryInterface::class);

		$this->likeService = new LikeService($this->likeRepository);
	}

	/**
	 * @dataProvider buildLikeDataProvider
	 */
	public function testBuildLikeData(
		string $type,
		int $contentId,
		int $userId,
		bool $isContentAlreadyLiked,
		array $shouldReturn
	): void {

		if (!empty($contentId) &&
			!empty($type)) {
			$this->likeRepository
				->expects($this->once())
				->method('count')
				->with($type, $contentId)
				->willReturn($shouldReturn['count']);
		}

		$likeData = $this->likeService->buildLikeData(
			$type,
			$contentId,
			$userId,
			$isContentAlreadyLiked
		);

		$this->assertEquals($shouldReturn, $likeData);
	}

	public function buildLikeDataProvider(): array
	{
		$youOtherPeople = 'You and {link} like this.';
		$youOtherPeople = str_replace(
			'{link}',
			'<a href="localhost?action=likes;sa=view;ltype=msg;like=666;foo=baz">3 other people</a>',
			$youOtherPeople
		);

		return [
			'happy happy joy joy' => [
				'type' => 'bre_sta',
				'contentId' => 666,
				'userId' => 1,
				'isContentAlreadyLiked' => true,
				'shouldReturn' => [
					'contentId' => 666,
					'count' => 1,
					'alreadyLiked' => true,
					'type' => 'bre_sta',
					'canLike' => false,
					'additionalInfo' => 'You like this.',
				],
			],
			'no type' => [
				'type' => '',
				'contentId' => 666,
				'userId' => 1,
				'isContentAlreadyLiked' => true,
				'shouldReturn' =>  [
					'contentId' => 666,
					'count' => 0,
					'alreadyLiked' => false,
					'type' => '',
					'canLike' => false,
					'additionalInfo' => '',
				],
			],
			'other people liked it' => [
				'type' => 'bre_com',
				'contentId' => 666,
				'userId' => 1,
				'isContentAlreadyLiked' => false,
				'shouldReturn' =>  [
					'contentId' => 666,
					'count' => 2,
					'alreadyLiked' => false,
					'type' => 'bre_com',
					'canLike' => false,
					'additionalInfo' =>
						'<a href="localhost?action=likes;sa=view;ltype=msg;like=666;foo=baz">2 people</a> like this.',
				],
			],
			'you and other people liked it' => [
				'type' => 'bre_com',
				'contentId' => 666,
				'userId' => 1,
				'isContentAlreadyLiked' => true,
				'shouldReturn' =>  [
					'contentId' => 666,
					'count' => 3,
					'alreadyLiked' => true,
					'type' => 'bre_com',
					'canLike' => false,
					'additionalInfo' =>
						$youOtherPeople,
				],
			],
		];
	}
}
