<?php

declare(strict_types=1);

namespace Breeze\Util\Validate;

use Breeze\Service\StatusService as StatusService;
use Breeze\Service\UserService;
use Breeze\Util\Validate\Validations\PostStatus;
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
	public function testClean(array $data, bool $isExpectedException): void
	{
		$this->postStatus->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($data, $this->postStatus->getData());
		}

		$this->postStatus->clean();
	}

	public function cleanProvider(): array
	{
		return [
			'empty values' => [
				'data' => [
					'status_owner_id' => '0',
					'status_poster_id' => 0,
					'status_body' => '',
				],
				'isExpectedException' => true,
			],
			'happy path' => [
				'data' => [
					'status_owner_id' => 1,
					'status_poster_id' => 2,
					'status_body' => 'Happy Path',
				],
				'isExpectedException' => false,
			],
			'incomplete data' => [
				'data' => [
					'status_owner_id' => 1
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
					'status_owner_id' => 2,
					'status_poster_id' => 1,
				],
				'isExpectedException' => false,
			],
			'not ints' => [
				'data' => [
					'status_owner_id' => 'fail',
					'status_poster_id' => 'lol',
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
					'status_body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
			'not a string' => [
				'data' => [
					'status_body' => 666,
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
			$this->assertEquals($data['status_poster_id'], $this->postStatus->getPosterId());
		}

		$this->postStatus->floodControl();
	}

	public function floodControlProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'data' => [
					'status_poster_id' => 1,
					'status_owner_id' => 2,
					'comments_profile_id' => 3,
					'status_id' => 666,
					'comments_body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
			'time has not expired, too much messages' => [
				'data' => [
					'status_poster_id' => 2,
					'status_owner_id' => 2,
					'comments_profile_id' => 3,
					'status_id' => 666,
					'comments_body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => true,
			],
			'time has expired, too much messages' => [
				'data' => [
					'status_poster_id' => 3,
					'status_owner_id' => 2,
					'comments_profile_id' => 3,
					'status_id' => 666,
					'comments_body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
			'time has not expired,  allowed messages' => [
				'data' => [
					'status_poster_id' => 4,
					'status_owner_id' => 2,
					'comments_profile_id' => 3,
					'status_id' => 666,
					'comments_body' => 'Kaizoku ou ni ore wa naru',
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
					'status_poster_id' => 1,
					'status_owner_id' => 2,
					'comments_profile_id' => 3,
					'status_id' => 666,
					'comments_body' => 'Kaizoku ou ni ore wa naru',
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
		], $this->postStatus->getSteps());
	}

	public function testGetParams(): void
	{
		$this->assertEquals([
			'status_owner_id' => 0,
			'status_poster_id' => 0,
			'status_body' => '',
		], $this->postStatus->getParams());
	}

	private function getMockInstance(string $class): MockObject
	{
		return $this->getMockBuilder($class)
			->disableOriginalConstructor()
			->getMock();
	}
}
