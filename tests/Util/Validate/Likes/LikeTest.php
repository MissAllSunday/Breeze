<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Likes;

use Breeze\Service\LikeService;
use Breeze\Service\UserService;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\Likes\Like;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LikeTest extends TestCase
{
	private Like $like;

	public function setUp(): void
	{
		/**  @var MockObject&UserService $userService */
		$userService = $this->createMock(UserService::class);

		/**  @var MockObject&LikeService $likeService */
		$likeService = $this->createMock(LikeService::class);

		$this->like = new Like($userService, $likeService);
	}

	/**
	 * @dataProvider cleanProvider
	 */
	public function testCompare(array $data, bool $isExpectedException): void
	{
		$this->like->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($data, $this->like->getData());
		}

		$this->like->compare();
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
		$this->like->setData($data);

		if ($isExpectedException) {
			$this->expectException(ValidateDataException::class);
		} else {
			$this->assertEquals($integers, $this->like->getInts());
		}

		$this->like->isInt();
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
		$this->like->setData($data);

		$this->expectException(ValidateDataException::class);
		$this->like->permissions();
	}

	/**
	 * @dataProvider permissionsProvider
	 */
	public function testIsFeatureEnable(array $data): void
	{
		$this->like->setData($data);

		$this->expectException(ValidateDataException::class);
		$this->like->isFeatureEnable();
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
		$this->like->setData($data);

		$this->expectException(ValidateDataException::class);
		$this->like->checkType();
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
