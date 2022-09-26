<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Likes;

use Breeze\Repository\LikeRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\Likes\Like;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class LikeTest extends TestCase
{
	use ProphecyTrait;

	/**
	 * @dataProvider cleanProvider
	 */
	public function testCompare(array $data, bool $isExpectedException): void
	{
		$likeRepository = $this->prophesize(LikeRepositoryInterface::class);
		$like = new Like($likeRepository->reveal());
		$like->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals($data, $like->getData());
		}

		$like->compare();
	}

	public function cleanProvider(): array
	{
		return [
			'empty values' => [
				'data' => [
					'content_id' => 0,
					'sa' => '',
					'content_type' => '',
				],
				'isExpectedException' => true,
			],
			'happy path' => [
				'data' => [
					'content_id' => 666,
					'sa' => 'like',
					'content_type' => 'br_sta',
					'id_member' => 666,
				],
				'isExpectedException' => false,
			],
			'incomplete data' => [
				'data' => [
					'content_id' => 666,
				],
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * @dataProvider isValidIntProvider
	 */
	public function testIsValidInt(array $data, array $integers, bool $isExpectedException): void
	{
		$likeRepository = $this->prophesize(LikeRepositoryInterface::class);
		$like = new Like($likeRepository->reveal());
		$like->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->assertEquals($integers, $like->getInts());
		}

		$like->isInt();
	}

	public function isValidIntProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'content_id' => 666,
					'id_member' => 666,
				],
				'integers' => [
					'content_id',
					'id_member',
				],
				'isExpectedException' => false,
			],
			'not an int' => [
				'data' => [
					'content_id' => 'custom',
				],
				'integers' => [
					'content_id',
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
		$likeRepository = $this->prophesize(LikeRepositoryInterface::class);
		$like = new Like($likeRepository->reveal());
		$like->setData($data);

		$this->expectException(DataNotFoundException::class);
		$like->permissions();
	}

	/**
	 * @dataProvider permissionsProvider
	 */
	public function testIsFeatureEnable(array $data): void
	{
		$likeRepository = $this->prophesize(LikeRepositoryInterface::class);
		$like = new Like($likeRepository->reveal());
		$like->setData($data);

		$this->expectException(DataNotFoundException::class);

		$like->isFeatureEnable();
	}

	public function permissionsProvider(): array
	{
		return [
			'not allowed' => [
				'data' => [
					'like' => 666,
				],
			],
		];
	}

	/**
	 * @dataProvider checkTypeProvider
	 */
	public function testCheckType(array $data): void
	{
		$likeRepository = $this->prophesize(LikeRepositoryInterface::class);
		$like = new Like($likeRepository->reveal());
		$like->setData($data);

		$this->expectException(DataNotFoundException::class);

		$like->checkType();
	}

	public function checkTypeProvider(): array
	{
		return [
			'invalid type' => [
				'data' => [
					'like' => 666,
					'content_type' => 'not valid',
				],
			],
		];
	}
}
