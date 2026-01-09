<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Status;

use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\Status\DeleteStatus;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class DeleteStatusTest extends TestCase
{
	/**
	 * @throws Exception
	 */
	#[DataProvider('checkAllowProvider')]
	public function testCheckAllow(array $data, string $permissionName, bool $isExpectedException): void
	{
		$validateData = $this->createStub(Data::class);
		$validateUser = $this->createStub(User::class);
		$validateAllow = $this->createStub(Allow::class);
		$statusRepository = $this->createStub(StatusRepositoryInterface::class);

		$deleteStatus = new DeleteStatus(
			$validateData,
			$validateUser,
			$validateAllow,
			$statusRepository
		);
		$deleteStatus->setData($data);

		if ($isExpectedException) {
			$this->expectException(NotAllowedException::class);
		}

		$statusRepository->method('getCurrentUserInfo')->willReturn(['id' => 666]);

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
					'user_id' => 666,
				],
				'permissionName' => 'deleteOwnStatus',
				'isExpectedException' => true,
			],
			'deleteAny' => [
				'data' => [
					'user_id' => 1,
				],
				'permissionName' => 'deleteStatus',
				'isExpectedException' => true,
			],
			'pass' => [
				'data' => [
					'user_id' => 1,
				],
				'permissionName' => 'yep',
				'isExpectedException' => false,
			],
		];
	}

	/**
	 * @throws Exception
	 */
	#[DataProvider('checkUserProvider')]
	public function testCheckUser(array $data, array $validUsers, bool $isExpectedException): void
	{
		$validateData = $this->createStub(Data::class);
		$validateUser = $this->createStub(User::class);
		$validateAllow = $this->createStub(Allow::class);
		$statusRepository = $this->createStub(StatusRepositoryInterface::class);

		$deleteStatus = new DeleteStatus(
			$validateData,
			$validateUser,
			$validateAllow,
			$statusRepository
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
					'user_id' => 666,
				],
				'validUsers' => [666],
				'isExpectedException' => false,
			],
			'invalidUsers' => [
				'data' => [
					'user_id' => 2,
				],
				'validUsers' => [2],
				'isExpectedException' => true,
			],
		];
	}
}
