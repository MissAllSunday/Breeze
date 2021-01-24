<?php

declare(strict_types=1);

namespace Breeze\Util\Validate;

use Breeze\Service\UserService;
use Breeze\Util\Validate\Validations\StatusByProfile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StatusByProfileTest extends TestCase
{
	private StatusByProfile $statusByProfile;

	public function setUp(): void
	{
		/**  @var UserService&MockObject $userService */
		$userService = $this->createMock(UserService::class);

		$this->statusByProfile = new StatusByProfile($userService);
	}

	/**
	 * @dataProvider cleanProvider
	 */
	public function testClean(array $data, bool $isExpectedException): void
	{
		$this->statusByProfile->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($data, $this->statusByProfile->getData());
		}

		$this->statusByProfile->clean();
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
					'wallId' => 1,
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
		$this->statusByProfile->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals(array_keys($data), $this->statusByProfile->getInts());
		}

		$this->statusByProfile->isInt();
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
		$this->assertEquals([
			'clean',
			'isInt',
			'areValidUsers',
			'ignoreList',
		], $this->statusByProfile->getSteps());
	}

	public function testGetParams(): void
	{
		$this->assertEquals([
			'wallId' => 0,
		], $this->statusByProfile->getParams());
	}
}
