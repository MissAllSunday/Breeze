<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\Status\PostStatus;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class PostStatusTest extends TestCase
{
	/**
	 * @throws Exception
	 */
	public function testGetParams(): void
	{
		$repository = $this->createStub(StatusRepositoryInterface::class);
		$validateAllow = $this->createStub(Allow::class);
		$validateUser = $this->createStub(User::class);
		$validateData = $this->createStub(Data::class);

		$postStatus = new PostStatus(
			$validateData,
			$validateUser,
			$validateAllow,
			$repository
		);

		$this->assertEquals([
			'wall_id' => 0,
			'user_id' => 0,
			'body' => '',
		], $postStatus->getParams());
	}

	#[DataProvider('checkAllowProvider')]
	public function testCheckAllow(array $data, int $currentUserId, bool $isExpectedException): void
	{
		$repository = $this->createStub(StatusRepositoryInterface::class);
		$repository->method('getCurrentUserInfo')->willReturn(['id' => $currentUserId]);

		$validateAllow = $this->createMock(Allow::class);

		if ($currentUserId === $data[StatusEntity::WALL_ID]) {
			$validateAllow->expects($this->never())->method('permissions');
		} elseif ($isExpectedException) {
			$validateAllow->expects($this->once())
				->method('permissions')
				->with('postStatus', 'postStatus')
				->willThrowException(new NotAllowedException());
			$this->expectException(NotAllowedException::class);
		} else {
			$validateAllow->expects($this->once())
				->method('permissions')
				->with('postStatus', 'postStatus');
		}

		$validateUser = $this->createStub(User::class);
		$validateData = $this->createStub(Data::class);

		$postStatus = new PostStatus($validateData, $validateUser, $validateAllow, $repository);
		$postStatus->setData($data);

		$postStatus->checkAllow();
	}

	public static function checkAllowProvider(): array
	{
		return [
			'owner posting on own wall' => [
				'data' => [
					StatusEntity::WALL_ID => 1,
					StatusEntity::USER_ID => 1,
					StatusEntity::BODY => 'Test',
				],
				'currentUserId' => 1,
				'isExpectedException' => false,
			],
			'non-owner with post permission' => [
				'data' => [
					StatusEntity::WALL_ID => 2,
					StatusEntity::USER_ID => 1,
					StatusEntity::BODY => 'Test',
				],
				'currentUserId' => 1,
				'isExpectedException' => false,
			],
			'non-owner without post permission' => [
				'data' => [
					StatusEntity::WALL_ID => 2,
					StatusEntity::USER_ID => 1,
					StatusEntity::BODY => 'Test',
				],
				'currentUserId' => 1,
				'isExpectedException' => true,
			],
		];
	}
}
