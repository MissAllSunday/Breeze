<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\User;

use Breeze\Service\UserService;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\User\UserSettings;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserSettingsTest extends TestCase
{
	private UserSettings $userSettings;

	public function setUp(): void
	{
		/**  @var MockObject&UserService $userService */
		$userService = $this->createMock(UserService::class);

		$this->userSettings = new UserSettings($userService);
	}

	/**
	 * @dataProvider cleanProvider
	 */
	public function testCompare(array $data, bool $isExpectedException): void
	{
		$this->userSettings->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals($data, $this->userSettings->getData());
		}

		$this->userSettings->compare();
	}

	public function cleanProvider(): array
	{
		return [
			'default values' => [
				'data' => [
					'wall' => 0,
					'generalWall' => 0,
					'paginationNumber' => 5,
					'kickIgnored' => 0,
					'aboutMe' => '',
				],
				'isExpectedException' => false,
			],
			'happy path' => [
				'data' => [
					'wall' => 666,
					'generalWall' => 1,
					'paginationNumber' => 5,
					'kickIgnored' => 0,
					'aboutMe' => 'this was custom set',
				],
				'isExpectedException' => false,
			],
		];
	}

	/**
	 * @dataProvider isValidIntProvider
	 */
	public function testIsValidInt(array $data, bool $isExpectedException): void
	{
		$this->userSettings->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals(array_keys($data), $this->userSettings->getInts());
		}

		$this->userSettings->isInt();
	}

	public function isValidIntProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'wall' => 0,
					'generalWall' => 0,
					'paginationNumber' => 5,
					'kickIgnored' => 0,
				],
				'isExpectedException' => false,
			],
			'not ints' => [
				'data' => [
					'wall' => '666',
					'generalWall' => 0,
					'paginationNumber' => 'lol',
					'kickIgnored' => 0,
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
		$this->userSettings->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals(array_keys($data), $this->userSettings->getStrings());
		}

		$this->userSettings->isString();
	}

	public function isValidStringProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'aboutMe' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
			'not a string' => [
				'data' => [
					'aboutMe' => 666,
				],
				'isExpectedException' => true,
			],
		];
	}

	public function testGetSteps(): void
	{
		$this->assertEquals([
			'compare',
			'isInt',
			'isString',
		], $this->userSettings->getSteps());
	}

	/**
	 * @dataProvider getParamsProvider
	 */
	public function testGetParams(array $data): void
	{
		$this->userSettings->setData($data);

		$this->assertEquals($data, $this->userSettings->getParams());
	}

	public function getParamsProvider(): array
	{
		return [
			'default' => [
				'data' => [
					'wall' => 0,
					'generalWall' => 0,
					'paginationNumber' => 5,
					'kickIgnored' => 0,
					'aboutMe' => '',
				],
			],
			'custom params' => [
				'data' => [
					'wall' => 666,
					'generalWall' => 666,
					'paginationNumber' => 5,
					'kickIgnored' => 666,
					'aboutMe' => '',
				],
			],
		];
	}
}
