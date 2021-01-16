<?php

declare(strict_types=1);


namespace Breeze\Util\Validate;

use Breeze\Repository\InvalidMoodException;
use Breeze\Service\MoodService;
use Breeze\Service\UserService;
use Breeze\Util\Validate\Validations\DeleteMood;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteMoodTest extends TestCase
{
	private DeleteMood $deleteMood;

	public function setUp(): void
	{
		/**  @var MockObject&UserService $userService */
		$userService = $this->createMock(UserService::class);

		/**  @var MockObject&MoodService $moodService */
		$moodService = $this->createMock(MoodService::class);

		$this->deleteMood = new DeleteMood($userService, $moodService);
	}

	/**
	 * @dataProvider dataExistsProvider
	 */
	public function testDataExists(array $data, bool $isExpectedException): void
	{
		$this->deleteMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(InvalidMoodException::class);
		} else {
			$this->assertEquals($data, $this->deleteMood->getData());
		}

		$this->deleteMood->dataExists();
	}

	public function dataExistsProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'id' => 666,
				],
				'isExpectedException' => false,
			],
		];
	}

	/**
	 * @dataProvider cleanProvider
	 */
	public function testClean(array $data, bool $isExpectedException): void
	{
		$this->deleteMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($data, $this->deleteMood->getData());
		}

		$this->deleteMood->clean();
	}

	public function cleanProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'id' => 666,
				],
				'isExpectedException' => false,
			],
			'custom data' => [
				'data' => [
					'id' => 'custom',
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
		$this->deleteMood->setData($data);

		$this->expectException(ValidateDataException::class);
		$this->deleteMood->permissions();
	}

	public function permissionsProvider(): array
	{
		return [
			'not allowed' => [
				'data' => [
					'id' => 666,
				],
			],
		];
	}

	/**
	 * @dataProvider isValidIntProvider
	 */
	public function testIsValidInt(array $data, array $integers, bool $isExpectedException): void
	{
		$this->deleteMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($integers, $this->deleteMood->getInts());
		}

		$this->deleteMood->isInt();
	}

	public function isValidIntProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'id' => 666,
				],
				'integers' => [
					'id'
				],
				'isExpectedException' => false,
			],
			'not an int' => [
				'data' => [
					'id' => 'custom',
				],
				'integers' => [
					'id'
				],
				'isExpectedException' => true,
			],
		];
	}

	public function testGetSteps(): void
	{
		$this->assertEquals([
			'clean',
			'permissions',
			'dataExists',
		], $this->deleteMood->getSteps());
	}
}
