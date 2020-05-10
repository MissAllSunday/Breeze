<?php

declare(strict_types=1);

use Breeze\Service\UserService;
use Breeze\Util\Validate\ValidateComment as ValidateComment;
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
	public function testClean(array $data, bool $expectedResult, string $expectedErrorKey): void
	{
		$this->validateComment->setData($data);

		$isClean = $this->validateComment->clean();
		$errorKey = $this->validateComment->getErrorKey();

		$this->assertEquals($expectedResult, $isClean);
		$this->assertEquals($expectedErrorKey, $errorKey);
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
				'expectedResult' => true,
				'expectedErrorKey' => '',
			],
			'incomplete data' => [
				'data' => [
					'posterId' => '1'
				],
				'expectedResult' => false,
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
				'expectedResult' => false,
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

		$isInt = $this->validateComment->isInt();
		$errorKey = $this->validateComment->getErrorKey();

		$this->assertEquals($expectedResult, $isInt);
		$this->assertEquals($expectedErrorKey, $errorKey);
	}

	/**
	 * @dataProvider IsValidStringProvider
	 */
	public function testIsValidString(array $data, bool $expectedResult, string $expectedErrorKey): void
	{
		$this->validateComment->setData($data);
		$isString = $this->validateComment->isString();
		$errorKey = $this->validateComment->getErrorKey();

		$this->assertEquals($expectedResult, $isString);
		$this->assertEquals($expectedErrorKey, $errorKey);
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
		bool $expectedResult,
		array $loadUsersInfoWillReturn,
		string
$expectedErrorKey
	): void
	{
		$this->validateComment->setData($data);
		$this->userService->expects($this->once())
			->method('loadUsersInfo')
			->with($with)
			->willReturn($loadUsersInfoWillReturn);

		$areValidUsers = $this->validateComment->areValidUsers();
		$errorKey = $this->validateComment->getErrorKey();

		$this->assertEquals($expectedResult, $areValidUsers);
		$this->assertEquals($expectedErrorKey, $errorKey);
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
				'expectedResult' => true,
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
				'expectedResult' => false,
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
				],
				'expectedErrorKey' => 'invalid_users',
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
