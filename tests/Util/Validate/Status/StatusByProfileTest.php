<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Status;

use Breeze\Repository\StatusRepository;
use Breeze\Util\Validate\DataNotFoundException;
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
		$statusRepository = $this->prophesize(StatusRepository::class);
		$statusByProfile = new StatusByProfile($statusRepository->reveal());
		$statusByProfile->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
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
		$statusRepository = $this->prophesize(StatusRepository::class);
		$statusByProfile = new StatusByProfile($statusRepository->reveal());
		$statusByProfile->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
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
		$statusRepository = $this->prophesize(StatusRepository::class);
		$statusByProfile = new StatusByProfile($statusRepository->reveal());

		$this->assertEquals([
			'compare',
			'isInt',
			'areValidUsers',
			'ignoreList',
		], $statusByProfile->getSteps());
	}

	public function testGetParams(): void
	{
		$statusRepository = $this->prophesize(StatusRepository::class);
		$statusByProfile = new StatusByProfile($statusRepository->reveal());

		$this->assertEquals([
			'wallId' => 0,
		], $statusByProfile->getParams());
	}
}
