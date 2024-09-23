<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Status;

use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\Status\DeleteStatus;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DeleteStatusTest extends TestCase
{
	#[DataProvider('checkAllowProvider')]
	public function testCheckAllow(array $data, string $permissionName, bool $isExpectedException): void
	{
		$validateData = $this->createMock(Data::class);
		$validateUser = $this->createMock(User::class);
		$validateAllow = $this->createMock(Allow::class);
		$userRepository = $this->createMock(BaseRepositoryInterface::class);

		$deleteStatus = new DeleteStatus(
			$validateData,
			$validateUser,
			$validateAllow,
			$userRepository
		);
		$deleteStatus->setData($data);

		if ($isExpectedException) {
			$this->expectException(NotAllowedException::class);
		}

		$userRepository->method('getCurrentUserInfo')->willReturn(['id' => 666]);

		if ($isExpectedException) {
			$validateAllow->method('permissions')->willThrowException(new NotAllowedException());
		}

		$deleteStatus->checkAllow();

		$this->assertEquals($deleteStatus->data, $data);
	}

	public static function checkAllowProvider(): array
	{
		return [
			'deleteOwn' => [
				'data' => [
					'userId' => 666,
				],
				'permissionName' => 'deleteOwnStatus',
				'isExpectedException' => true,
			],
			'deleteAny' => [
				'data' => [
					'userId' => 1,
				],
				'permissionName' => 'deleteStatus',
				'isExpectedException' => true,
			],
			'pass' => [
				'data' => [
					'userId' => 1,
				],
				'permissionName' => 'yep',
				'isExpectedException' => false,
			],
		];
	}

	#[DataProvider('checkUserProvider')]
	public function testCheckUser(array $data, array $validUsers, bool $isExpectedException): void
	{
		$validateData = $this->createMock(Data::class);
		$validateUser = $this->createMock(User::class);
		$validateAllow = $this->createMock(Allow::class);
		$userRepository = $this->createMock(BaseRepositoryInterface::class);

		$deleteStatus = new DeleteStatus(
			$validateData,
			$validateUser,
			$validateAllow,
			$userRepository
		);
		$deleteStatus->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
			$validateUser->method('areValidUsers')->willThrowException(new DataNotFoundException());
		}

		$deleteStatus->checkUser();

		$this->assertEquals($deleteStatus->data, $data);
	}

	public static function checkUserProvider(): array
	{
		return [
			'validUsers' => [
				'data' => [
					'userId' => 666,
				],
				'validUsers' => [666],
				'isExpectedException' => false,
			],
			'invalidUsers' => [
				'data' => [
					'userId' => 2,
				],
				'validUsers' => [2],
				'isExpectedException' => true,
			],
		];
	}
}
