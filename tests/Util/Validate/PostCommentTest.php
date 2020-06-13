<?php

declare(strict_types=1);

use Breeze\Service\UserService;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\PostComment;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PostCommentTest extends TestCase
{
	/**
	 * @var MockBuilder|UserService
	 */
	private $userService;

	public function setUp(): void
	{
		$this->userService = $this->getMockInstance(UserService::class);
	}

	/**
	 * @dataProvider cleanProvider
	 */
	public function testClean(array $data, bool $isExpectedException): void
	{
		$validateComment = new PostComment($this->userService, $data);

		if ($isExpectedException)
		{
			$this->expectException(ValidateDataException::class);

			$validateComment->clean();
		}

		else
			$this->assertNull($validateComment->clean());
	}

	public function cleanProvider(): array
	{
		return [
			'empty values' => [
				'data' => [
					'posterId' => 0,
					'statusOwnerId' => '0',
					'profileOwnerId' => '',
					'statusId' => '666',
					'body' => 'LOL',
				],
				'isExpectedException' => true,
			],
			'happy path' => [
				'data' => [
					'posterId' => 1,
					'statusOwnerId' => 2,
					'profileOwnerId' => 3,
					'statusId' => 666,
					'body' => 'Happy Path',
				],
				'isExpectedException' => false,
			],
			'incomplete data' => [
				'data' => [
					'posterId' => '1'
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
		$validateComment = new PostComment($this->userService, $data);

		if ($isExpectedException)
		{
			$this->expectException(ValidateDataException::class);

			$validateComment->isInt();
		}

		else
			$this->assertNull($validateComment->isInt());
	}

	public function IsValidIntProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'posterId' => 1,
					'statusOwnerId' => 2,
					'profileOwnerId' => 3,
					'statusId' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
			'not ints' => [
				'data' => [
					'posterId' => 'lol',
					'statusOwnerId' => 'fail',
					'profileOwnerId' => '',
					'statusId' => '666',
					'body' => 'LOL',
				],
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * @dataProvider IsValidStringProvider
	 */
	public function testIsValidString(array $data, bool $isExpectedException): void
	{
		$validateComment = new PostComment($this->userService, $data);

		if ($isExpectedException)
		{
			$this->expectException(ValidateDataException::class);

			$validateComment->isString();
		}

		else
			$this->assertNull($validateComment->isString());
	}

	public function IsValidStringProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'posterId' => 1,
					'statusOwnerId' => 2,
					'profileOwnerId' => 3,
					'statusId' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
			'not a string' => [
				'data' => [
					'posterId' => 1,
					'statusOwnerId' => 2,
					'profileOwnerId' => 3,
					'statusId' => 666,
					'body' => 666,
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
		$validateComment = new PostComment($this->userService, $data);

		$this->userService->expects($this->once())
			->method('getUsersToLoad')
			->with($with)
			->willReturn($loadUsersInfoWillReturn);

		if ($isExpectedException)
		{
			$this->expectException(ValidateDataException::class);

			$validateComment->areValidUsers();
		}

		else
			$this->assertNull($validateComment->areValidUsers());
	}

	public function areValidUsersProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'data' => [
					'posterId' => 1,
					'statusOwnerId' => 2,
					'profileOwnerId' => 3,
					'statusId' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
				'with' => [
					1,
					2,
					3,
				],
				'loadUsersInfoWillReturn' => [
					1,
					2,
					3,
				],
				'isExpectedException' => false,
			],
			'invalid users' => [
				'data' => [
					'posterId' => 1,
					'statusOwnerId' => 2,
					'profileOwnerId' => 666,
					'statusId' => 666,
					'body' => '666',
				],
				'with' => [
					1,
					2,
					666,
				],
				'loadUsersInfoWillReturn' => [
					1,
					2,
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
		$validateComment = new PostComment($this->userService, $data);

		if ($isExpectedException)
		{
			$this->expectException(ValidateDataException::class);

			$validateComment->floodControl();
		}

		else
			$this->assertNull($validateComment->floodControl());
	}

	public function floodControlProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'data' => [
					'posterId' => 1,
					'statusOwnerId' => 2,
					'profileOwnerId' => 3,
					'statusId' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
			'time has not expired, too much messages' => [
				'data' => [
					'posterId' => 2,
					'statusOwnerId' => 2,
					'profileOwnerId' => 3,
					'statusId' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => true,
			],
			'time has expired, too much messages' => [
				'data' => [
					'posterId' => 3,
					'statusOwnerId' => 2,
					'profileOwnerId' => 3,
					'statusId' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
			'time has not expired,  allowed messages' => [
				'data' => [
					'posterId' => 4,
					'statusOwnerId' => 2,
					'profileOwnerId' => 3,
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
		$validateComment = new PostComment($this->userService, $data);

		$this->expectException(ValidateDataException::class);
		$validateComment->permissions();
	}

	public function permissionsProvider(): array
	{
		return [
			'not allowed' => [
				'data' => [
					'posterId' => 1,
					'statusOwnerId' => 2,
					'profileOwnerId' => 3,
					'statusId' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
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