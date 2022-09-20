<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Status;

use Breeze\Service\StatusService as StatusService;
use Breeze\Service\UserService;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\Status\PostStatus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PostStatusTest extends TestCase
{
	private PostStatus $postStatus;

	public function setUp(): void
	{
		/**  @var UserService&MockObject $userService */
		$userService = $this->createMock(UserService::class);

		/**  @var StatusService&MockObject $statusService */
		$statusService = $this->getMockInstance(StatusService::class);

		$this->postStatus = new PostStatus($userService, $statusService);
	}

	/**
	 * @dataProvider cleanProvider
	 */
	public function testCompare(array $data, bool $isExpectedException): void
	{
		$this->postStatus->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($data, $this->postStatus->getData());
		}

		$this->postStatus->compare();
	}

	public function cleanProvider(): array
	{
		return [
			'empty values' => [
				'data' => [
					'wallId' => '0',
					'userId' => 0,
					'body' => '',
				],
				'isExpectedException' => true,
			],
			'happy path' => [
				'data' => [
					'wallId' => 1,
					'userId' => 2,
					'body' => 'Happy Path',
				],
				'isExpectedException' => false,
			],
			'incomplete data' => [
				'data' => [
					'wallId' => 1,
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
		$this->postStatus->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals(array_keys($data), $this->postStatus->getInts());
		}

		$this->postStatus->isInt();
	}

	public function isValidIntProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'wallId' => 2,
					'userId' => 1,
				],
				'isExpectedException' => false,
			],
			'not ints' => [
				'data' => [
					'wallId' => 'fail',
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
		$this->postStatus->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals(array_keys($data), $this->postStatus->getStrings());
		}

		$this->postStatus->isString();
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
		$this->postStatus->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($data['userId'], $this->postStatus->getPosterId());
		}

		$this->postStatus->floodControl();
	}

	public function floodControlProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'data' => [
					'userId' => 1,
					'wallId' => 2,
					'comments_profile_id' => 3,
					'id' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
			'time has not expired, too much messages' => [
				'data' => [
					'userId' => 2,
					'wallId' => 2,
					'comments_profile_id' => 3,
					'id' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => true,
			],
			'time has expired, too much messages' => [
				'data' => [
					'userId' => 3,
					'wallId' => 2,
					'comments_profile_id' => 3,
					'id' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
			'time has not expired,  allowed messages' => [
				'data' => [
					'userId' => 4,
					'wallId' => 2,
					'comments_profile_id' => 3,
					'id' => 666,
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
		$this->postStatus->setData($data);

		$this->expectException(ValidateDataException::class);
		$this->postStatus->permissions();
	}

	public function permissionsProvider(): array
	{
		return [
			'not allowed' => [
				'data' => [
					'userId' => 1,
					'wallId' => 2,
					'comments_profile_id' => 3,
					'id' => 666,
					'body' => 'Kaizoku ou ni ore wa naru',
				],
			],
		];
	}

	public function testGetSteps(): void
	{
		$this->assertEquals([
			'compare',
			'isInt',
			'isString',
			'permissions',
			'areValidUsers',
			'floodControl',
		], $this->postStatus->getSteps());
	}

	public function testGetParams(): void
	{
		$this->assertEquals([
			'wallId' => 0,
			'userId' => 0,
			'body' => '',
		], $this->postStatus->getParams());
	}

	private function getMockInstance(string $class): MockObject
	{
		return $this->getMockBuilder($class)
			->disableOriginalConstructor()
			->getMock();
	}
}
