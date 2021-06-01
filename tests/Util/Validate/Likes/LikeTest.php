<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Likes;

use Breeze\Service\LikesService;
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

		/**  @var MockObject&LikesService $likeService */
		$likeService = $this->createMock(LikesService::class);

		$this->like = new Like($userService, $likeService);
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
					'like' => 666,
				],
				'integers' => [
					'like',
				],
				'isExpectedException' => false,
			],
			'not an int' => [
				'data' => [
					'like' => 'custom',
				],
				'integers' => [
					'like',
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
