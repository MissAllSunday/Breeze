<?php

declare(strict_types=1);

namespace Breeze;

use Breeze\Service\CommentService;
use Breeze\Service\StatusService;
use Breeze\Service\UserService;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\DeleteComment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteCommentTest extends TestCase
{
	/**
	 * @var UserService&MockObject
	 */
	private $userService;

	private DeleteComment $deleteComment;

	public function setUp(): void
	{
		$this->userService = $this->createMock(UserService::class);

		/**  @var MockObject&StatusService $statusService */
		$statusService = $this->getMockInstance(StatusService::class);

		/**  @var MockObject&CommentService $commentService */
		$commentService = $this->getMockInstance(CommentService::class);

		$this->deleteComment = new DeleteComment($this->userService, $statusService, $commentService);
	}

	/**
	 * @dataProvider cleanProvider
	 */
	public function testClean(array $data, bool $isExpectedException): void
	{
		$this->deleteComment->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($data, $this->deleteComment->getData());
		}

		$this->deleteComment->clean();
	}

	public function cleanProvider(): array
	{
		return [
			'empty values' => [
				'data' => [
					'comments_poster_id' => 0,
					'comments_id' => '0',
				],
				'isExpectedException' => true,
			],
			'happy path' => [
				'data' => [
					'comments_poster_id' => 666,
					'comments_id' => 666,
				],
				'isExpectedException' => false,
			],
			'incomplete data' => [
				'data' => [
					'comments_poster_id' => 1
				],
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * @dataProvider isValidIntProvider
	 */
	public function testIsValidInt(array $data, bool $isExpectedException): void
	{
		$this->deleteComment->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals(array_keys($data), $this->deleteComment->getInts());
		}

		$this->deleteComment->isInt();
	}

	public function isValidIntProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'comments_id' => 666,
					'comments_poster_id' => 666,
				],
				'isExpectedException' => false,
			],
			'not ints' => [
				'data' => [
					'comments_id' => 666,
					'comments_poster_id' => '666',
				],
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * @dataProvider areValidUsersProvider
	 */
	public function testAreValidUsers(
		array $data,
		array $with,
		array $loadUsersInfoWillReturn,
		bool $isExpectedException
	): void {
		$this->deleteComment->setData($data);

		$this->userService->expects($this->once())
			->method('getUsersToLoad')
			->with($with)
			->willReturn($loadUsersInfoWillReturn);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		}

		$this->deleteComment->areValidUsers();
	}

	public function areValidUsersProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'data' => [
					'comments_poster_id' => 666,
					'comments_id' => 666,
				],
				'with' => [
					666,
				],
				'loadUsersInfoWillReturn' => [
					666,
				],
				'isExpectedException' => false,
			],
			'invalid users' => [
				'data' => [
					'comments_poster_id' => 666,
					'comments_id' => 666,
				],
				'with' => [
					666,
				],
				'loadUsersInfoWillReturn' => [
					1,
				],
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * @dataProvider permissionsProvider
	 */
	public function testPermissions(array $data, array $userInfo): void
	{
		$this->userService->expects($this->once())
			->method('getCurrentUserInfo')
			->willReturn($userInfo);

		$this->deleteComment->setData($data);

		$this->expectException(ValidateDataException::class);
		$this->deleteComment->permissions();
	}

	public function permissionsProvider(): array
	{
		return [
			'not allowed' => [
				'data' => [
					'comments_poster_id' => 666,
					'comments_id' => 666,
				],
				'userInfo' => [
					'id' => 666,
				]
			],
		];
	}

	/**
	 * @dataProvider getStepsProvider
	 */
	public function testGetSteps(array $steps): void
	{
		$this->assertEquals($steps, $this->deleteComment->getSteps());
	}

	public function getStepsProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'steps' => [
					'clean',
					'isInt',
					'validComment',
					'validUser',
					'permissions',
				]
			]
		];
	}

	private function getMockInstance(string $class): MockObject
	{
		return $this->getMockBuilder($class)
			->disableOriginalConstructor()
			->getMock();
	}
}
