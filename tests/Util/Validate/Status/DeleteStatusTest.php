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
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DeleteStatusTest extends TestCase
{
	use ProphecyTrait;

	/**
	 * @dataProvider checkAllowProvider
	 */
	public function testCheckAllow(array $data, string $permissionName, bool $isExpectedException): void
	{
		$validateData = $this->prophesize(Data::class);
		$validateUser = $this->prophesize(User::class);
		$validateAllow = $this->prophesize(Allow::class);
		$userRepository = $this->prophesize(BaseRepositoryInterface::class);

		$deleteStatus = new DeleteStatus(
			$validateData->reveal(),
			$validateUser->reveal(),
			$validateAllow->reveal(),
			$userRepository->reveal()
		);
		$deleteStatus->setData($data);

		if ($isExpectedException) {
			$this->expectException(NotAllowedException::class);
		}

		$userRepository->getCurrentUserInfo()->willReturn(['id' => 666]);

		if ($isExpectedException) {
			$validateAllow->permissions($permissionName, 'deleteStatus')->willThrow(new NotAllowedException());
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

	/**
	 * @dataProvider checkUserProvider
	 */
	public function testCheckUser(array $data, array $validUsers, bool $isExpectedException): void
	{
		$validateData = $this->prophesize(Data::class);
		$validateUser = $this->prophesize(User::class);
		$validateAllow = $this->prophesize(Allow::class);
		$userRepository = $this->prophesize(BaseRepositoryInterface::class);

		$deleteStatus = new DeleteStatus(
			$validateData->reveal(),
			$validateUser->reveal(),
			$validateAllow->reveal(),
			$userRepository->reveal()
		);
		$deleteStatus->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
			$validateUser->areValidUsers($validUsers)->willThrow(new DataNotFoundException());
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
