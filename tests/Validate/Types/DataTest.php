<?php

declare(strict_types=1);

namespace Breeze\Validate\Types;

use Breeze\Repository\InvalidDataException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DataTest extends TestCase
{
	use ProphecyTrait;

	/**
	 * @dataProvider commentProvider
	 */
	public function testComment(array $defaultParams, array $data, bool $isExpectedException): void
	{
		$validateData = new Data();

		if ($isExpectedException) {
			$this->expectException(InvalidDataException::class);
		} else {
			$this->expectNotToPerformAssertions();
		}

		$validateData->compare($defaultParams, $data);
	}

	public static function commentProvider(): array
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
					'body' => '',
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

	/**
	 * @dataProvider likesProvider
	 */
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
}


//'Invalid' => [
//	'defaultParams' => [
//	],
//	'data' => [],
//	'isExpectedException' => false,
//],
