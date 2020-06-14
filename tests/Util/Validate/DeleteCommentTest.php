<?php

declare(strict_types=1);

use Breeze\Service\CommentService;
use Breeze\Service\UserService;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\DeleteComment;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteCommentTest extends TestCase
{
	/**
	 * @var MockBuilder|UserService
	 */
	private $userService;

	/**
	 * @var MockObject|CommentService
	 */
	private $commentService;

	/**
	 * @var DeleteComment
	 */
	private $deleteComment;

	public function setUp(): void
	{
		$this->userService = $this->getMockInstance(UserService::class);
		$this->commentService = $this->getMockInstance(CommentService::class);

		$this->deleteComment = new DeleteComment($this->userService, $this->commentService);
	}

	/**
	 * @dataProvider cleanProvider
	 */
	public function testClean(array $data, bool $isExpectedException): void
	{
		$this->deleteComment->setData($data);

		if ($isExpectedException)
		{
			$this->expectException(ValidateDataException::class);

			$this->deleteComment->clean();
		}

		else
			$this->assertNull($this->deleteComment->clean());
	}

	public function cleanProvider(): array
	{
		return [
			'empty values' => [
				'data' => [
					'posterId' => 0,
					'commentId' => '0',
				],
				'isExpectedException' => true,
			],
			'happy path' => [
				'data' => [
					'posterId' => 666,
					'commentId' => 666,
				],
				'isExpectedException' => false,
			],
			'incomplete data' => [
				'data' => [
					'posterId' => 1
				],
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * @dataProvider IsValidIntProvider
	 */
	public function testIsValidInt(array $data, bool $isExpectedException): void
	{
		$this->deleteComment->setData($data);

		if ($isExpectedException)
		{
			$this->expectException(ValidateDataException::class);

			$this->deleteComment->isInt();
		}

		else
			$this->assertNull($this->deleteComment->isInt());
	}

	public function IsValidIntProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'posterId' => 666,
					'commentId' => 666,
				],
				'isExpectedException' => false,
			],
			'not ints' => [
				'data' => [
					'posterId' => '666',
					'commentId' => 666,
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
	): void
	{
		$this->deleteComment->setData($data);

		$this->userService->expects($this->once())
			->method('getUsersToLoad')
			->with($with)
			->willReturn($loadUsersInfoWillReturn);

		if ($isExpectedException)
		{
			$this->expectException(ValidateDataException::class);

			$this->deleteComment->areValidUsers();
		}

		else
			$this->assertNull($this->deleteComment->areValidUsers());
	}

	public function areValidUsersProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'data' => [
					'posterId' => 666,
					'commentId' => 666,
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
					'posterId' => 666,
					'commentId' => 666,
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
					'posterId' => 666,
					'commentId' => 666,
				],
				'userInfo' => [
					'id' => 666,
				]
			],
		];
	}

	private function getMockInstance(string $class): MockObject
	{
		return $this->getMockBuilder($class)
			->disableOriginalConstructor()
			->getMock();
	}
}
