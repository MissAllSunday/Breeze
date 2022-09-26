<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Mood;

use Breeze\Repository\User\MoodRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\Mood\CreateMood;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class CreateMoodTest extends TestCase
{
	use ProphecyTrait;

	/**
	 * @dataProvider cleanProvider
	 */
	public function testCompare(array $data, bool $isExpectedException): void
	{
		$moodRepository = $this->prophesize(MoodRepositoryInterface::class);
		$createMood = new CreateMood($moodRepository->reveal());
		$createMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals($data, $createMood->getData());
		}

		$createMood->compare();
	}

	public function cleanProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'emoji' => 'lol',
					'description' => 'desc',
					'isActive' => 0,
				],
				'isExpectedException' => false,
			],
			'custom data' => [
				'data' => [
					'emoji' => 'custom',
					'description' => 'custom desc',
					'isActive' => 1,
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
		$moodRepository = $this->prophesize(MoodRepositoryInterface::class);
		$createMood = new CreateMood($moodRepository->reveal());
		$createMood->setData($data);

		$this->expectException(DataNotFoundException::class);

		$createMood->permissions();
	}

	public function permissionsProvider(): array
	{
		return [
			'not allowed' => [
				'data' => [
					'emoji' => 'lol',
					'description' => 'desc',
					'isActive' => 0,
				],
			],
		];
	}

	/**
	 * @dataProvider isValidIntProvider
	 */
	public function testIsValidInt(array $data, array $integers, bool $isExpectedException): void
	{
		$moodRepository = $this->prophesize(MoodRepositoryInterface::class);
		$createMood = new CreateMood($moodRepository->reveal());
		$createMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals($integers, $createMood->getInts());
		}

		$createMood->isInt();
	}

	public function isValidIntProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'emoji' => 'not empty',
					'description' => 'desc',
					'isActive' => 1,
				],
				'integers' => [
					'isActive',
				],
				'isExpectedException' => false,
			],
			'not an int' => [
				'data' => [
					'emoji' => 111,
					'description' => 222,
					'isActive' => 'not an int',
				],
				'integers' => [
					'enable',
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
		$moodRepository = $this->prophesize(MoodRepositoryInterface::class);
		$createMood = new CreateMood($moodRepository->reveal());
		$createMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals($strings, $createMood->getStrings());
		}

		$createMood->isString();
	}

	public function isValidStringProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'emoji' => 'not empty',
					'description' => 'desc',
					'isActive' => 1,
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
					'isActive' => 'lol',
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
		$moodRepository = $this->prophesize(MoodRepositoryInterface::class);
		$createMood = new CreateMood($moodRepository->reveal());

		$this->assertEquals([
			'compare',
			'isInt',
			'isString',
		], $createMood->getSteps());
	}

	/**
	 * @dataProvider getParamsProvider
	 */
	public function testGetParams(array $data): void
	{
		$moodRepository = $this->prophesize(MoodRepositoryInterface::class);
		$createMood = new CreateMood($moodRepository->reveal());
		$createMood->setData($data);

		$this->assertEquals($data, $createMood->getParams());
	}

	public function getParamsProvider(): array
	{
		return [
			'default' => [
				'data' => [
					'emoji' => 'lol',
					'description' => 'desc',
					'isActive' => 0,
				],
			],
			'custom params' => [
				'data' => [
					'emoji' => 'custom emoji',
					'description' => 'some other description',
					'isActive' => 1,
				],
			],
		];
	}
}
