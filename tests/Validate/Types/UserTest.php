<?php

declare(strict_types=1);

namespace Breeze\Validate\Types;

use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
	private MockObject|BaseRepositoryInterface $baseRepository;

	private User $user;

	/**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		$this->baseRepository = $this->createMock(BaseRepositoryInterface::class);

		$this->user = new User($this->baseRepository);
	}

	#[DataProvider('areValidUsersProvider')]
	public function testAreValidUsers(array $users, array $usersToLoad, bool $isExpectedException): void
	{
		$this->baseRepository->expects($this->once())
			->method('getUsersToLoad')
			->willReturn($usersToLoad);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		}

		$this->user->areValidUsers($users);
	}

	public static function areValidUsersProvider(): array
	{
		return [
			'users are valid' => [
				'users' => [1,2,3],
				'usersToLoad' => [1,2,3],
				'isExpectedException' => false,
			],
			'users are NOT valid' => [
				'users' => [1,2,3],
				'usersToLoad' => [4,5,6],
				'isExpectedException' => true,
			],
		];
	}

	#[DataProvider('isSameUserProvider')]
	public function testIsSameUser(int $posterUserId, bool $isExpectedException): void
	{
		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->expectNotToPerformAssertions();
		}

		$this->user->isSameUser($posterUserId);
	}

	public static function isSameUserProvider(): array
	{
		return [
			'user is same' => [
				'posterUserId' => 666,
				'isExpectedException' => false,
			],
			'user is different' => [
				'posterUserId' => 1,
				'isExpectedException' => true,
			],
		];
	}
}
