<?php

declare(strict_types=1);

use Breeze\Service\UserService;
use Breeze\Util\Validate\ValidateComment as ValidateComment;
use Breeze\Util\Validate\ValidateDataException;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidateCommentTest extends TestCase
{
	/**
	 * @var ValidateComment
	 */
	private $validateComment;

	/**
	 * @var MockBuilder|UserService
	 */
	private $userService;

	public function setUp(): void
	{
		$this->userService = $this->getMockInstance(UserService::class);

		$this->validateComment = new ValidateComment($this->userService);
	}

	/**
	 * @dataProvider cleanProvider
	 */
	public function testClean(array $data, string $expectedErrorKey): void
	{
		$this->validateComment->setData($data);

		if (!empty($expectedErrorKey))
		{
			$this->expectException(InvalidArgumentException::class);

			$this->validateComment->clean();
			$errorKey = $this->validateComment->getErrorKey();

			$this->expectExceptionMessage($errorKey);
		}

		else
		{
			$this->assertNull($this->validateComment->clean());

			$errorKey = $this->validateComment->getErrorKey();

			$this->assertEquals($expectedErrorKey, $errorKey);
		}
	}

	public function cleanProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'posterId' => 1,
					'statusOwnerId' => 2,
					'profileOwnerId' => 3,
					'statusId' => 666,
					'body' => 'Happy Path',
				],
				'expectedErrorKey' => '',
			],
			'incomplete data' => [
				'data' => [
					'posterId' => '1'
				],
				'expectedErrorKey' => 'incomplete_data',
			],
			'empty values' => [
				'data' => [
					'posterId' => 0,
					'statusOwnerId' => '0',
					'profileOwnerId' => '',
					'statusId' => '666',
					'body' => 'LOL',
				],
				'expectedErrorKey' => 'incomplete_data',
			],
		];
	}

	/**
	 * @dataProvider IsValidIntProvider
	 */
	public function testIsValidInt(array $data, bool $expectedResult, string $expectedErrorKey): void
	{
		$this->validateComment->setData($data);

		if (!empty($expectedErrorKey))
		{
			$this->expectException(InvalidArgumentException::class);

			$this->validateComment->isInt();
			$errorKey = $this->validateComment->getErrorKey();

			$this->expectExceptionMessage($errorKey);
		}

		else
		{
			$this->assertNull($this->validateComment->isInt());

			$errorKey = $this->validateComment->getErrorKey();

			$this->assertEquals($expectedErrorKey, $errorKey);
		}
	}

	/**
	 * @dataProvider IsValidStringProvider
	 */
	public function testIsValidString(array $data, bool $expectedResult, string $expectedErrorKey): void
	{
		$this->validateComment->setData($data);

		if (!empty($expectedErrorKey))
		{
			$this->expectException(InvalidArgumentException::class);

			$this->validateComment->isString();
			$errorKey = $this->validateComment->getErrorKey();

			$this->expectExceptionMessage($errorKey);
		}

		else
		{
			$this->assertNull($this->validateComment->isString());

			$errorKey = $this->validateComment->getErrorKey();

			$this->assertEquals($expectedErrorKey, $errorKey);
		}
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
				'expectedResult' => true,
				'expectedErrorKey' => '',
			],
			'not ints' => [
				'data' => [
					'posterId' => 'lol',
					'statusOwnerId' => 'fail',
					'profileOwnerId' => '',
					'statusId' => '666',
					'body' => 'LOL',
				],
				'expectedResult' => false,
				'expectedErrorKey' => 'malformed_data',
			],
		];
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
				'expectedResult' => true,
				'expectedErrorKey' => '',
			],
			'not a string' => [
				'data' => [
					'posterId' => 1,
					'statusOwnerId' => 2,
					'profileOwnerId' => 3,
					'statusId' => 666,
					'body' => 666,
				],
				'expectedResult' => false,
				'expectedErrorKey' => 'malformed_data',
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
		string $expectedErrorKey
	): void
	{
		$this->validateComment->setData($data);
		$this->userService->expects($this->once())
			->method('loadUsersInfo')
			->with($with)
			->willReturn($loadUsersInfoWillReturn);

		if (!empty($expectedErrorKey))
		{
			$this->expectException(ValidateDataException::class);

			$this->validateComment->areValidUsers();
			$errorKey = $this->validateComment->getErrorKey();

			$this->expectExceptionMessage($errorKey);
		}

		else
		{
			$this->assertNull($this->validateComment->areValidUsers());

			$errorKey = $this->validateComment->getErrorKey();

			$this->assertEquals($expectedErrorKey, $errorKey);
		}
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
				'with' => [1,2,3],
				'loadUsersInfoWillReturn' => [
					1 => [
						'link' => 'Link',
						'name' => 'Name',
						'avatar' => ['href' => '/default.png']
					],
					2 => [
						'link' => 'Link',
						'name' => 'Name',
						'avatar' => ['href' => '/default.png']
					],
					3 =>[
						'link' => 'Link',
						'name' => 'Name',
						'avatar' => ['href' => '/default.png']
					],
				],
				'expectedErrorKey' => '',
			],
			'invalid users' => [
				'data' => [
					'posterId' => 1,
					'statusOwnerId' => 2,
					'profileOwnerId' => 666,
					'statusId' => 666,
					'body' => '666',
				],
				'with' => [1,2,666],
				'loadUsersInfoWillReturn' => [
					1 => [
						'link' => 'Link',
						'name' => 'Name',
						'avatar' => ['href' => '/default.png']
					],
					2 => [
						'link' => 'Link',
						'name' => 'Name',
						'avatar' => ['href' => '/default.png']
					],
					666 => false,
				],
				'expectedErrorKey' => 'invalid_users',
			],
		];
	}

	/**
	 * @dataProvider floodControlProvider
	 */
	public function testFloodControl(array $data, string $expectedErrorKey): void
	{
		$this->validateComment->setData($data);

		if (!empty($expectedErrorKey))
		{
			$this->expectException(ValidateDataException::class);

			$this->validateComment->floodControl();
			$errorKey = $this->validateComment->getErrorKey();

			$this->expectExceptionMessage($errorKey);
		}

		else
		{
			$this->assertNull($this->validateComment->floodControl());

			$errorKey = $this->validateComment->getErrorKey();

			$this->assertEquals($expectedErrorKey, $errorKey);
		}
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
				'expectedErrorKey' => '',
			],
			'time has not expired, too much messages' => [
				'data' => [
					'posterId' => 2,
					'statusOwnerId' => 2,
					'profileOwnerId' => 3,
					'statusId' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
				'expectedErrorKey' => 'flood',
			],
			'time has expired, too much messages' => [
				'data' => [
					'posterId' => 3,
					'statusOwnerId' => 2,
					'profileOwnerId' => 3,
					'statusId' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
				'expectedErrorKey' => '',
			],
			'time has not expired,  allowed messages' => [
				'data' => [
					'posterId' => 4,
					'statusOwnerId' => 2,
					'profileOwnerId' => 3,
					'statusId' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
				'expectedErrorKey' => '',
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
