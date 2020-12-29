<?php

declare(strict_types=1);


namespace Breeze\Util\Validate;

use Breeze\Service\MoodService;
use Breeze\Service\UserService;
use Breeze\Util\Validate\Validations\CreateMood;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateMoodTest extends TestCase
{
	private CreateMood $createMood;

	public function setUp(): void
	{
		/**  @var MockObject&UserService $userService */
		$userService = $this->createMock(UserService::class);

		/**  @var MockObject&MoodService $moodService */
		$moodService = $this->createMock(MoodService::class);

		$this->createMood = new CreateMood($userService, $moodService);
	}

	/**
	 * @dataProvider cleanProvider
	 */
	public function testClean(array $data, bool $isExpectedException): void
	{
		$this->createMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($data, $this->createMood->getData());
		}

		$this->createMood->clean();
	}

	public function cleanProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'emoji' => 'lol',
					'description' => 'desc',
					'enable' => 0,
				],
				'isExpectedException' => false,
			],
			'custom data' => [
				'data' => [
					'emoji' => 'custom',
					'description' => 'custom desc',
					'enable' => 1,
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
		$this->createMood->setData($data);

		$this->expectException(ValidateDataException::class);
		$this->createMood->permissions();
	}

	public function permissionsProvider(): array
	{
		return [
			'not allowed' => [
				'data' => [
					'emoji' => 'lol',
					'description' => 'desc',
					'enable' => 0,
				],
			],
		];
	}

	/**
	 * @dataProvider isValidIntProvider
	 */
	public function testIsValidInt(array $data, array $integers, bool $isExpectedException): void
	{
		$this->createMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($integers, $this->createMood->getInts());
		}

		$this->createMood->isInt();
	}

	public function isValidIntProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'emoji' => 'not empty',
					'description' => 'desc',
					'enable' => 1,
				],
				'integers' => [
					'enable'
				],
				'isExpectedException' => false,
			],
			'not an int' => [
				'data' => [
					'emoji' => 111,
					'description' => 222,
					'enable' => 'not an int',
				],
				'integers' => [
					'enable'
				],
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * @dataProvider isValidStringProvider
	 */
	public function testIsValidString(array $data, array $strings, bool $isExpectedException): void
	{
		$this->createMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($strings, $this->createMood->getStrings());
		}

		$this->createMood->isString();
	}

	public function isValidStringProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'emoji' => 'not empty',
					'description' => 'desc',
					'enable' => 1,
				],
				'strings' => [
					'emoji',
					'description',
				],
				'isExpectedException' => false,
			],
			'not a string' => [
				'data' => [
					'emoji' => 666,
					'description' => [666, 667],
					'enable' => 'lol',
				],
				'strings' => [
					'emoji',
					'description',
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
			'isString',
		], $this->createMood->getSteps());
	}

	/**
	 * @dataProvider getParamsProvider
	 */
	public function testGetParams(array $data): void
	{
		$this->createMood->setData($data);

		$this->assertEquals($data, $this->createMood->getParams());
	}

	public function getParamsProvider(): array
	{
		return [
			'default' => [
				'data' => [
					'emoji' => 'lol',
					'description' => 'desc',
					'enable' => 0,
				],
			],
			'custom params' => [
				'data' => [
					'emoji' => 'custom emoji',
					'description' => 'some other description',
					'enable' => 1,
				],
			],
		];
	}
}
