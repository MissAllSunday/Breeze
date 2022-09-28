<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\User;

use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\User\UserSettings;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class UserSettingsTest extends TestCase
{
	use ProphecyTrait;

	/**
	 * @dataProvider cleanProvider
	 */
	public function testCompare(array $data, bool $isExpectedException): void
	{
		$userSettings = new UserSettings();
		$userSettings->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals($data, $userSettings->getData());
		}

		$userSettings->compare();
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
		$userSettings = new UserSettings();
		$userSettings->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals(array_keys($data), $userSettings->getInts());
		}

		$userSettings->isInt();
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
		$userSettings = new UserSettings();
		$userSettings->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals(array_keys($data), $userSettings->getStrings());
		}

		$userSettings->isString();
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
		$userSettings = new UserSettings();

		$this->assertEquals([
			'compare',
			'isInt',
			'isString',
		], $userSettings->getSteps());
	}

	/**
	 * @dataProvider getParamsProvider
	 */
	public function testGetParams(array $data): void
	{
		$userSettings = new UserSettings();
		$userSettings->setData($data);

		$this->assertEquals($data, $userSettings->getParams());
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
