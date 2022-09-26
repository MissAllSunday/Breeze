<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Status;

use Breeze\Repository\StatusRepository;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\Status\DeleteStatus;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DeleteStatusTest extends TestCase
{
	use ProphecyTrait;

	/**
	 * @dataProvider cleanProvider
	 */
	public function testCompare(array $data, bool $isExpectedException): void
	{
		$statusRepository = $this->prophesize(StatusRepository::class);
		$deleteStatus = new DeleteStatus($statusRepository->reveal());
		$deleteStatus->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals($data, $deleteStatus->getData());
		}

		$deleteStatus->compare();
	}

	public function cleanProvider(): array
	{
		return [
			'empty values' => [
				'data' => [
					'id' => 0,
					'userId' => '0',
				],
				'isExpectedException' => true,
			],
			'happy path' => [
				'data' => [
					'id' => 666,
					'userId' => 666,
				],
				'isExpectedException' => false,
			],
			'incomplete data' => [
				'data' => [
					'id' => 1,
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
		$statusRepository = $this->prophesize(StatusRepository::class);
		$deleteStatus = new DeleteStatus($statusRepository->reveal());
		$deleteStatus->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals(array_keys($data), $deleteStatus->getInts());
		}

		$deleteStatus->isInt();
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
		array $loadUsersInfoWillReturn,
		bool $isExpectedException
	): void {
		$statusRepository = $this->prophesize(StatusRepositoryInterface::class);
		$statusRepository->getUsersToLoad($with)
			->willReturn($loadUsersInfoWillReturn);

		$deleteStatus = new DeleteStatus($statusRepository->reveal());
		$deleteStatus->setData($data);


		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals($data, $deleteStatus->getData());
		}

		$deleteStatus->areValidUsers();
	}

	public function areValidUsersProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'data' => [
					'userId' => 666,
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
					'userId' => 666,
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
		$statusRepository = $this->prophesize(StatusRepository::class);
		$deleteStatus = new DeleteStatus($statusRepository->reveal());
		$deleteStatus->setData($data);

		$this->expectException(DataNotFoundException::class);
		$deleteStatus->permissions();
	}

	public function permissionsProvider(): array
	{
		return [
			'not allowed' => [
				'data' => [
					'userId' => 666,
					'id' => 666,
				],
				'userInfo' => [
					'id' => 666,
				],
			],
		];
	}

	public function testGetSteps(): void
	{
		$statusRepository = $this->prophesize(StatusRepository::class);
		$deleteStatus = new DeleteStatus($statusRepository->reveal());

		$this->assertEquals([
			'compare',
			'isInt',
			'validStatus',
			'validUser',
			'permissions',
		], $deleteStatus->getSteps());
	}

	public function testGetParams(): void
	{
		$statusRepository = $this->prophesize(StatusRepository::class);
		$deleteStatus = new DeleteStatus($statusRepository->reveal());

		$this->assertEquals([
			'id' => 0,
			'userId' => 0,
		], $deleteStatus->getParams());
	}
}
