<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Status;

use Breeze\Service\StatusService;
use Breeze\Service\UserService;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\Status\StatusByProfile;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class StatusByProfileTest extends TestCase
{
	use ProphecyTrait;

	/**
	 * @dataProvider cleanProvider
	 */
	public function testCompare(array $data, bool $isExpectedException): void
	{
		$data = array_filter($data);
		$userService = $this->prophesize(UserService::class);
		$statusService = $this->prophesize(StatusService::class);

		$statusByProfile = new StatusByProfile($userService->reveal(), $statusService->reveal());
		$statusByProfile->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($data, $statusByProfile->getData());
		}

		$statusByProfile->compare();
	}

	public function cleanProvider(): array
	{
		return [
			'empty values' => [
				'data' => [
					'wallId' => '0',
				],
				'isExpectedException' => true,
			],
			'happy path' => [
				'data' => [
					'wallId' => 666,
				],
				'isExpectedException' => false,
			],
			'incomplete data' => [
				'data' => [],
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

		$statusByProfile = new StatusByProfile($userService->reveal(), $statusService->reveal());
		$statusByProfile->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals(array_keys($data), $statusByProfile->getInts());
		}

		$statusByProfile->isInt();
	}

	public function isValidIntProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'wallId' => 2,
				],
				'isExpectedException' => false,
			],
			'not ints' => [
				'data' => [
					'wallId' => 'fail',
				],
				'isExpectedException' => true,
			],
		];
	}

	public function testGetSteps(): void
	{
		$userService = $this->prophesize(UserService::class);
		$statusService = $this->prophesize(StatusService::class);

		$statusByProfile = new StatusByProfile($userService->reveal(), $statusService->reveal());

		$this->assertEquals([
			'compare',
			'isInt',
			'areValidUsers',
			'ignoreList',
		], $statusByProfile->getSteps());
	}

	public function testGetParams(): void
	{
		$userService = $this->prophesize(UserService::class);
		$statusService = $this->prophesize(StatusService::class);

		$statusByProfile = new StatusByProfile($userService->reveal(), $statusService->reveal());

		$this->assertEquals([
			'wallId' => 0,
		], $statusByProfile->getParams());
	}
}
