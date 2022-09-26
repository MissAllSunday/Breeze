<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Mood;

use Breeze\Repository\InvalidMoodException;
use Breeze\Repository\User\MoodRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\Mood\DeleteMood;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DeleteMoodTest extends TestCase
{
	use ProphecyTrait;

	/**
	 * @dataProvider dataExistsProvider
	 */
	public function testDataExists(array $data, array $getByIdReturn, bool $isExpectedException): void
	{
		$moodRepository = $this->prophesize(MoodRepositoryInterface::class);
		$moodRepository->getById($data['id'])->willReturn($getByIdReturn);

		$deleteMood = new DeleteMood($moodRepository->reveal());
		$deleteMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(InvalidMoodException::class);
		} else {
			$this->assertEquals($data, $deleteMood->getData());
		}

		$deleteMood->dataExists();
	}

	public function dataExistsProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'id' => 666,
				],
				'getByIdReturn' => [666],
				'isExpectedException' => false,
			],
		];
	}

	/**
	 * @dataProvider cleanProvider
	 */
	public function testCompare(array $data, bool $isExpectedException): void
	{
		$moodRepository = $this->prophesize(MoodRepositoryInterface::class);
		$deleteMood = new DeleteMood($moodRepository->reveal());
		$deleteMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals($data, $deleteMood->getData());
		}

		$deleteMood->compare();
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
		$moodRepository = $this->prophesize(MoodRepositoryInterface::class);
		$deleteMood = new DeleteMood($moodRepository->reveal());
		$deleteMood->setData($data);

		$this->expectException(DataNotFoundException::class);

		$deleteMood->permissions();
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
		$moodRepository = $this->prophesize(MoodRepositoryInterface::class);
		$deleteMood = new DeleteMood($moodRepository->reveal());
		$deleteMood->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals($integers, $deleteMood->getInts());
		}

		$deleteMood->isInt();
	}

	public function isValidIntProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'id' => 666,
				],
				'integers' => [
					'id',
				],
				'isExpectedException' => false,
			],
			'not an int' => [
				'data' => [
					'id' => 'custom',
				],
				'integers' => [
					'id',
				],
				'isExpectedException' => true,
			],
		];
	}

	public function testGetSteps(): void
	{
		$moodRepository = $this->prophesize(MoodRepositoryInterface::class);
		$deleteMood = new DeleteMood($moodRepository->reveal());

		$this->assertEquals([
			'compare',
			'permissions',
			'dataExists',
		], $deleteMood->getSteps());
	}
}
