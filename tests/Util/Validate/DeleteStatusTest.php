<?php

declare(strict_types=1);

namespace Breeze\Util\Validate;

use Breeze\Service\CommentService;
use Breeze\Service\StatusService;
use Breeze\Service\UserService;
use Breeze\Util\Validate\Validations\DeleteComment;
use Breeze\Util\Validate\Validations\DeleteStatus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteStatusTest extends TestCase
{
	/**
	 * @var UserService&MockObject
	 */
	private $userService;

	private DeleteStatus $deleteStatus;

	public function setUp(): void
	{
		$this->userService = $this->createMock(UserService::class);

		/**  @var MockObject&StatusService $statusService */
		$statusService = $this->getMockInstance(StatusService::class);

		$this->deleteStatus = new DeleteStatus($this->userService, $statusService);
	}

	/**
	 * @dataProvider cleanProvider
	 */
	public function testClean(array $data, bool $isExpectedException): void
	{
		$this->deleteStatus->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($data, $this->deleteStatus->getData());
		}

		$this->deleteStatus->clean();
	}

	public function cleanProvider(): array
	{
		return [
			'empty values' => [
				'data' => [
					'status_id' => 0,
					'status_poster_id' => '0',
				],
				'isExpectedException' => true,
			],
			'happy path' => [
				'data' => [
					'status_id' => 666,
					'status_poster_id' => 666,
				],
				'isExpectedException' => false,
			],
			'incomplete data' => [
				'data' => [
					'status_id' => 1
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
		$this->deleteStatus->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals(array_keys($data), $this->deleteStatus->getInts());
		}

		$this->deleteStatus->isInt();
	}

	public function isValidIntProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'status_id' => 666,
					'status_poster_id' => 666,
				],
				'isExpectedException' => false,
			],
			'not ints' => [
				'data' => [
					'status_id' => 666,
					'status_poster_id' => '666',
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
		$this->deleteStatus->setData($data);

		$this->userService->expects($this->once())
			->method('getUsersToLoad')
			->with($with)
			->willReturn($loadUsersInfoWillReturn);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		}

		$this->deleteStatus->areValidUsers();
	}

	public function areValidUsersProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'data' => [
					'status_poster_id' => 666,
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
					'status_poster_id' => 666,
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

		$this->deleteStatus->setData($data);

		$this->expectException(ValidateDataException::class);
		$this->deleteStatus->permissions();
	}

	public function permissionsProvider(): array
	{
		return [
			'not allowed' => [
				'data' => [
					'status_poster_id' => 666,
					'status_id' => 666,
				],
				'userInfo' => [
					'id' => 666,
				]
			],
		];
	}

	public function testGetSteps(): void
	{
		$this->assertEquals([
			'clean',
			'isInt',
			'validStatus',
			'validUser',
			'permissions',
		], $this->deleteStatus->getSteps());
	}

	public function testGetParams(): void
	{
		$this->assertEquals([
			'status_id' => 0,
			'status_poster_id' => 0
		], $this->deleteStatus->getParams());
	}

	private function getMockInstance(string $class): MockObject
	{
		return $this->getMockBuilder($class)
			->disableOriginalConstructor()
			->getMock();
	}
}
