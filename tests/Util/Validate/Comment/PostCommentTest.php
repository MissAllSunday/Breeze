<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Comment;

use Breeze\Service\CommentService as CommentService;
use Breeze\Service\StatusService as StatusService;
use Breeze\Service\UserService;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\Comment\PostComment;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class PostCommentTest extends TestCase
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

		$postComment = new PostComment(
			$userService->reveal(),
			$statusService->reveal(),
			$commentService->reveal()
		);

		$postComment->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($data, $postComment->getData());
		}

		$postComment->compare();
	}

	public function cleanProvider(): array
	{
		return [
			'empty values' => [
				'data' => [
					'userId' => 0,
					'statusId' => '666',
					'body' => 'LOL',
				],
				'isExpectedException' => true,
			],
			'happy path' => [
				'data' => [
					'userId' => 1,
					'statusId' => 666,
					'body' => 'Happy Path',
				],
				'isExpectedException' => false,
			],
			'incomplete data' => [
				'data' => [
					'userId' => '1',
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

		$postComment = new PostComment(
			$userService->reveal(),
			$statusService->reveal(),
			$commentService->reveal()
		);

		$postComment->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals(array_keys($data), $postComment->getInts());
		}

		$postComment->isInt();
	}

	public function isValidIntProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'statusId' => 666,
					'userId' => 1,
				],
				'isExpectedException' => false,
			],
			'not ints' => [
				'data' => [
					'statusId' => '666',
					'userId' => 'lol',
				],
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * @dataProvider isValidStringProvider
	 */
	public function testIsValidString(array $data, bool $isExpectedException): void
	{
		$userService = $this->prophesize(UserService::class);
		$statusService = $this->prophesize(StatusService::class);
		$commentService = $this->prophesize(CommentService::class);

		$postComment = new PostComment(
			$userService->reveal(),
			$statusService->reveal(),
			$commentService->reveal()
		);

		$postComment->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals(array_keys($data), $postComment->getStrings());
		}

		$postComment->isString();
	}

	public function isValidStringProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
			'not a string' => [
				'data' => [
					'body' => 666,
				],
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * @dataProvider floodControlProvider
	 */
	public function testFloodControl(array $data, bool $isExpectedException): void
	{
		$userService = $this->prophesize(UserService::class);
		$statusService = $this->prophesize(StatusService::class);
		$commentService = $this->prophesize(CommentService::class);

		$postComment = new PostComment(
			$userService->reveal(),
			$statusService->reveal(),
			$commentService->reveal()
		);

		$postComment->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertArrayHasKey('msgCount', $postComment->getPersistenceValue(
				'flood_' . $postComment->getPosterId()
			));
		}

		$postComment->floodControl();
	}

	public function floodControlProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'data' => [
					'userId' => 1,
					'statusId' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
			'time has not expired, too much messages' => [
				'data' => [
					'userId' => 2,
					'statusId' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => true,
			],
			'time has expired, too much messages' => [
				'data' => [
					'userId' => 3,
					'statusId' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
			'time has not expired,  allowed messages' => [
				'data' => [
					'userId' => 4,
					'statusId' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
		];
	}

	/**
	 * @dataProvider permissionsProvider
	 */
	public function testPermissions(array $data): void
	{
		$userService = $this->prophesize(UserService::class);
		$statusService = $this->prophesize(StatusService::class);
		$commentService = $this->prophesize(CommentService::class);

		$postComment = new PostComment(
			$userService->reveal(),
			$statusService->reveal(),
			$commentService->reveal()
		);

		$postComment->setData($data);

		$this->expectException(ValidateDataException::class);
		$postComment->permissions();
	}

	public function permissionsProvider(): array
	{
		return [
			'not allowed' => [
				'data' => [
					'userId' => 1,
					'statusId' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
			],
		];
	}

	public function testGetSteps(): void
	{
		$userService = $this->prophesize(UserService::class);
		$statusService = $this->prophesize(StatusService::class);
		$commentService = $this->prophesize(CommentService::class);

		$postComment = new PostComment(
			$userService->reveal(),
			$statusService->reveal(),
			$commentService->reveal()
		);

		$this->assertEquals([
			'compare',
			'isInt',
			'isString',
			'permissions',
			'areValidUsers',
			'floodControl',
			'validStatus',
		], $postComment->getSteps());
	}

	public function testGetParams(): void
	{
		$userService = $this->prophesize(UserService::class);
		$statusService = $this->prophesize(StatusService::class);
		$commentService = $this->prophesize(CommentService::class);

		$postComment = new PostComment(
			$userService->reveal(),
			$statusService->reveal(),
			$commentService->reveal()
		);

		$this->assertEquals([
			'statusId' => 0,
			'userId' => 0,
			'body' => '',
		], $postComment->getParams());
	}
}
