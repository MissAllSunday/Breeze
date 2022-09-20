<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Comment;

use Breeze\Service\CommentService;
use Breeze\Service\StatusService;
use Breeze\Service\UserService;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\Comment\DeleteComment;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class DeleteCommentTest extends TestCase
{
	use ProphecyTrait;

	/**
	 * @dataProvider cleanProvider
	 */
	public function testCompare(array $data, bool $isExpectedException): void
	{
		$userService = $this->prophesize(UserService::class);
		$statusService = $this->prophesize(StatusService::class);
		$commentService = $this->prophesize(CommentService::class);

		$deleteComment = new DeleteComment(
			$userService->reveal(),
			$statusService->reveal(),
			$commentService->reveal()
		);
		$deleteComment->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($data, $deleteComment->getData());
		}

		$deleteComment->compare();
	}

	public function cleanProvider(): array
	{
		return [
			'empty values' => [
				'data' => [
					'userId' => 0,
					'id' => '0',
				],
				'isExpectedException' => true,
			],
			'happy path' => [
				'data' => [
					'userId' => 666,
					'id' => 666,
				],
				'isExpectedException' => false,
			],
			'incomplete data' => [
				'data' => [
					'userId' => 1,
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
		$userService = $this->prophesize(UserService::class);
		$statusService = $this->prophesize(StatusService::class);
		$commentService = $this->prophesize(CommentService::class);

		$deleteComment = new DeleteComment(
			$userService->reveal(),
			$statusService->reveal(),
			$commentService->reveal()
		);
		$deleteComment->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals(array_keys($data), $deleteComment->getInts());
		}

		$deleteComment->isInt();
	}

	public function isValidIntProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'id' => 666,
					'userId' => 666,
				],
				'isExpectedException' => false,
			],
			'not ints' => [
				'data' => [
					'id' => 666,
					'userId' => '666',
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
		array $loadedUsers,
		bool $isExpectedException
	): void {
		$userService = $this->prophesize(UserService::class);
		$statusService = $this->prophesize(StatusService::class);
		$commentService = $this->prophesize(CommentService::class);

		$deleteComment = new DeleteComment(
			$userService->reveal(),
			$statusService->reveal(),
			$commentService->reveal()
		);
		$deleteComment->setData($data);

		$userService->getUsersToLoad(Argument::type('array'))
			->willReturn($loadedUsers);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertIsArray($deleteComment->getData());
		}

		$deleteComment->areValidUsers();
	}

	public function areValidUsersProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'data' => [
					'userId' => 666,
					'id' => 666,
				],
				'with' => [
					666,
				],
				'loadedUsers' => [
					666,
				],
				'isExpectedException' => false,
			],
			'invalid users' => [
				'data' => [
					'userId' => 666,
					'id' => 666,
				],
				'with' => [
					666,
				],
				'loadedUsers' => [
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
		$userService = $this->prophesize(UserService::class);
		$statusService = $this->prophesize(StatusService::class);
		$commentService = $this->prophesize(CommentService::class);

		$deleteComment = new DeleteComment(
			$userService->reveal(),
			$statusService->reveal(),
			$commentService->reveal()
		);
		$deleteComment->setData($data);

		$userService->getCurrentUserInfo()
			->willReturn($userInfo);

		$deleteComment->setData($data);

		$this->expectException(ValidateDataException::class);
		$deleteComment->permissions();
	}

	public function permissionsProvider(): array
	{
		return [
			'not allowed' => [
				'data' => [
					'id' => 666,
					'userId' => 3,
				],
				'userInfo' => [
					'id' => 666,
				],
			],
		];
	}

	public function testGetSteps(): void
	{
		$userService = $this->prophesize(UserService::class);
		$statusService = $this->prophesize(StatusService::class);
		$commentService = $this->prophesize(CommentService::class);

		$deleteComment = new DeleteComment(
			$userService->reveal(),
			$statusService->reveal(),
			$commentService->reveal()
		);

		$this->assertEquals([
			'compare',
			'isInt',
			'validComment',
			'validUser',
			'permissions',
		], $deleteComment->getSteps());
	}

	public function testGetParams(): void
	{
		$userService = $this->prophesize(UserService::class);
		$statusService = $this->prophesize(StatusService::class);
		$commentService = $this->prophesize(CommentService::class);

		$deleteComment = new DeleteComment(
			$userService->reveal(),
			$statusService->reveal(),
			$commentService->reveal()
		);

		$this->assertEquals([
			'id' => 0,
			'userId' => 0,
		], $deleteComment->getParams());
	}
}
