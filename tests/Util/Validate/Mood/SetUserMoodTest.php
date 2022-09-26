<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Mood;

use Breeze\Repository\User\MoodRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\Mood\SetUserMood;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class SetUserMoodTest extends TestCase
{
	use ProphecyTrait;

	/**
	 * @dataProvider cleanProvider
	 */
	public function testCompare(array $data, bool $isExpectedException): void
	{
		$moodRepository = $this->prophesize(MoodRepositoryInterface::class);
		$setUserMood = new SetUserMood($moodRepository->reveal());
		$setUserMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals($data, $setUserMood->getData());
		}

		$setUserMood->compare();
	}

	public function cleanProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'moodId' => 6789,
					'userId' => 666,
				],
				'isExpectedException' => false,
			],
			'custom data' => [
				'data' => [
					'moodId' => 666,
					'userId' => 666,
					'externalKey' => 1,
				],
				'isExpectedException' => false,
			],
			'incomplete data' => [
				'data' => [
					'moodId' => 12666,
				],
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * @dataProvider permissionsProvider
	 */
	public function testPermissions(array $data): void
	{
		$moodRepository = $this->prophesize(MoodRepositoryInterface::class);
		$setUserMood = new SetUserMood($moodRepository->reveal());
		$setUserMood->setData($data);

		$this->expectException(DataNotFoundException::class);
		$setUserMood->permissions();
	}

	public function permissionsProvider(): array
	{
		return [
			'not allowed' => [
				'data' => [
					'moodId' => 666,
					'userId' => 666,
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
		$setUserMood = new SetUserMood($moodRepository->reveal());
		$setUserMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals($integers, $setUserMood->getInts());
		}

		$setUserMood->isInt();
	}

	public function isValidIntProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'moodId' => 666,
					'userId' => 666,
				],
				'integers' => [
					'moodId',
					'userId',
				],
				'isExpectedException' => false,
			],
			'not an int' => [
				'data' => [
					'moodId' => 666,
					'userId' => '666',
				],
				'integers' => [
					'moodId',
					'userId',
				],
				'isExpectedException' => true,
			],
		];
	}

	public function testGetSteps(): void
	{
		$moodRepository = $this->prophesize(MoodRepositoryInterface::class);
		$setUserMood = new SetUserMood($moodRepository->reveal());

		$this->assertEquals([
			'compare',
			'isInt',
			'permissions',
			'dataExists',
			'areValidUsers',
			'isSameUser',
		], $setUserMood->getSteps());
	}

	/**
	 * @dataProvider isSameUserProvider
	 */
	public function testIsSameUser(array $data, $isExpectedException): void
	{
		$moodRepository = $this->prophesize(MoodRepositoryInterface::class);
		$setUserMood = new SetUserMood($moodRepository->reveal());
		$setUserMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals($data, $setUserMood->getData());
		}

		$setUserMood->isSameUser();
	}

	public function isSameUserProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'data' => [
					'moodId' => 666,
					'userId' => 666,
				],
				'isExpectedException' => false,
			],
			'different user' => [
				'data' => [
					'moodId' => 666,
					'userId' => 555,
				],
				'isExpectedException' => true,
			],
		];
	}
}
