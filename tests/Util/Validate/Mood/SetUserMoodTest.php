<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Mood;

use Breeze\Service\MoodService;
use Breeze\Service\UserService;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\Mood\SetUserMood;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetUserMoodTest extends TestCase
{
	private SetUserMood $setUserMood;

	public function setUp(): void
	{
		/**  @var MockObject&UserService $userService */
		$userService = $this->createMock(UserService::class);

		/**  @var MockObject&MoodService $moodService */
		$moodService = $this->createMock(MoodService::class);

		$this->setUserMood = new SetUserMood($userService, $moodService);
	}

	/**
	 * @dataProvider cleanProvider
	 */
	public function testClean(array $data, bool $isExpectedException): void
	{
		$this->setUserMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($data, $this->setUserMood->getData());
		}

		$this->setUserMood->clean();
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
		$this->setUserMood->setData($data);

		$this->expectException(ValidateDataException::class);
		$this->setUserMood->permissions();
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
		$this->setUserMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($integers, $this->setUserMood->getInts());
		}

		$this->setUserMood->isInt();
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
		$this->assertEquals([
			'clean',
			'isInt',
			'permissions',
			'dataExists',
			'areValidUsers',
			'isSameUser',
		], $this->setUserMood->getSteps());
	}

	/**
	 * @dataProvider isSameUserProvider
	 */
	public function testIsSameUser(array $data, $isExpectedException): void
	{
		$this->setUserMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($data, $this->setUserMood->getData());
		}

		$this->setUserMood->isSameUser();
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
