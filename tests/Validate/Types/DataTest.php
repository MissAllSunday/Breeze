<?php

declare(strict_types=1);

namespace Breeze\Validate\Types;

use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Repository\InvalidDataException;
use Breeze\Util\Validate\DataNotFoundException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
	private Data $data;

	private BaseRepositoryInterface | MockObject $baseRepository;

	/**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		$this->baseRepository = $this->createMock(BaseRepositoryInterface::class);
		$this->data = new Data();
	}

	#[DataProvider('dataExistsProvider')]
	public function testDataExists(int $id, bool $isExpectedException): void
	{
		if ($isExpectedException) {
			$this->baseRepository->expects($this->once())
				->method('getById')
				->willThrowException(new DataNotFoundException);

			$this->expectException(DataNotFoundException::class);
		} else {
			$this->expectNotToPerformAssertions();
		}

		$this->data->dataExists($id, $this->baseRepository);
	}

	public static function dataExistsProvider(): array
	{
		return [
			'does not exists' => [
				'id' => 1,
				'isExpectedException' => true,
			],
			'exists' => [
				'id' => 0,
				'isExpectedException' => false,
			],
		];
	}

	#[DataProvider('compareProvider')]
	public function testCompare(array $defaultParams, array $data, bool $isExpectedException): void
	{
		if ($isExpectedException) {
			$this->expectException(InvalidDataException::class);
		} else {
			$this->expectNotToPerformAssertions();
		}

		$this->data->compare($defaultParams, $data);
	}

	public static function compareProvider(): array
	{
		return [
			'deleteCommentValid' => [
				'defaultParams' => [
					'id' => 0,
					'userId' => 0,
				],
				'data' => [
					'id' => 666,
					'userId' => 666,
				],
				'isExpectedException' => false,
			],
			'deleteCommentInvalid' => [
				'defaultParams' => [
					'id' => 0,
					'userId' => 0,
				],
				'data' => [
					'otherParam' => '',
				],
				'isExpectedException' => true,
			],
			'postCommentValid' => [
				'defaultParams' => [
					'statusId' => 0,
					'userId' => 0,
					'body' => '123456',
				],
				'data' => [
					'statusId' => 0,
					'userId' => 0,
					'body' => '',
				],
				'isExpectedException' => false,
			],
			'postCommentInvalid' => [
				'defaultParams' => [
					'statusId' => 0,
					'userId' => 0,
					'body' => '',
				],
				'data' => [
					'nope' => 0,
				],
				'isExpectedException' => true,
			],
		];
	}

	#[DataProvider('likesProvider')]
	public function testLikes(array $defaultParams, array $data, bool $isExpectedException): void
	{
		$validateData = new Data();

		if ($isExpectedException) {
			$this->expectException(InvalidDataException::class);
		} else {
			$this->expectNotToPerformAssertions();
		}

		$validateData->compare($defaultParams, $data);
	}

	public static function likesProvider(): array
	{
		return [
			'likeValid' => [
				'defaultParams' => [
					'content_id' => 0,
					'content_type' => '',
					'sa' => '',
					'id_member' => 0,
				],
				'data' => [
					'content_id' => 1,
					'content_type' => 'type',
					'sa' => 'sa',
					'id_member' => 1,
				],
				'isExpectedException' => false,
			],
			'likeInvalid' => [
				'defaultParams' => [
					'content_id' => 0,
					'content_type' => '',
					'sa' => '',
					'id_member' => 0,
				],
				'data' => [
					'content_id' => 0,
					'content_type' => '',
					'sa' => '',
				],
				'isExpectedException' => true,
			],
		];
	}

	#[DataProvider('isIntProvider')]
	public function testIsInt(array $shouldBeIntValues, array $data, bool $isExpectedException): void
	{
		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->expectNotToPerformAssertions();
		}

		$this->data->isInt($shouldBeIntValues, $data);
	}

	public static function isIntProvider(): array
	{
		return [
			'happy path' => [
				'shouldBeIntValues' => ['number', 'numero'],
				'data' => ['number' => 666, 'numero' => 667],
				'isExpectedException' => false,
			],
			'not ints' => [
				'shouldBeIntValues' => ['number', 'numero'],
				'data' => ['number' => '666', 'numero' => null],
				'isExpectedException' => true,
			],
		];
	}

	#[DataProvider('isStringProvider')]
	public function testIsString(array $shouldBeStringValues, array $data, bool $isExpectedException): void
	{
		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->expectNotToPerformAssertions();
		}

		$this->data->isString($shouldBeStringValues, $data);
	}

	public static function isStringProvider(): array
	{
		return [
			'happy path' => [
				'shouldBeStringValues' => ['string', 'texto'],
				'data' => ['string' => 'string', 'texto' => 'texto'],
				'isExpectedException' => false,
			],
			'not string' => [
				'shouldBeStringValues' => ['string', 'texto'],
				'data' => ['string' => false, 'texto' => null],
				'isExpectedException' => true,
			],
			'only numbers' => [
				'shouldBeStringValues' => ['numbers'],
				'data' => ['numbers' => '666', 'mixed' => null],
				'isExpectedException' => false,
			],
		];
	}
}
