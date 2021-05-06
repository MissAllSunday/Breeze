<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Comment;

use Breeze\Service\CommentService as CommentService;
use Breeze\Service\StatusService as StatusService;
use Breeze\Service\UserService;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\Comment\PostComment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PostCommentTest extends TestCase
{
	private PostComment $postComment;

	public function setUp(): void
	{
		/**  @var UserService&MockObject $userService */
		$userService = $this->createMock(UserService::class);

		/**  @var StatusService&MockObject $statusService */
		$statusService = $this->getMockInstance(StatusService::class);

		/**  @var CommentService&MockObject $commentService */
		$commentService = $this->getMockInstance(CommentService::class);

		$this->postComment = new PostComment($userService, $statusService, $commentService);
	}

	/**
	 * @dataProvider cleanProvider
	 */
	public function testClean(array $data, bool $isExpectedException): void
	{
		$this->postComment->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($data, $this->postComment->getData());
		}

		$this->postComment->clean();
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
		$this->postComment->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals(array_keys($data), $this->postComment->getInts());
		}

		$this->postComment->isInt();
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
		$this->postComment->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals(array_keys($data), $this->postComment->getStrings());
		}

		$this->postComment->isString();
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
		$this->postComment->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertArrayHasKey('msgCount', $this->postComment->getPersistenceValue(
				'flood_' . $this->postComment->getPosterId()
			));
		}

		$this->postComment->floodControl();
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
		$this->postComment->setData($data);

		$this->expectException(ValidateDataException::class);
		$this->postComment->permissions();
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
		$this->assertEquals([
			'clean',
			'isInt',
			'isString',
			'permissions',
			'areValidUsers',
			'floodControl',
			'validStatus',
		], $this->postComment->getSteps());
	}

	public function testGetParams(): void
	{
		$this->assertEquals([
			'statusId' => 0,
			'userId' => 0,
			'body' => '',
		], $this->postComment->getParams());
	}

	private function getMockInstance(string $class): MockObject
	{
		return $this->getMockBuilder($class)
			->disableOriginalConstructor()
			->getMock();
	}
}
