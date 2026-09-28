<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Enums\PermissionsEnum;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Service\PermissionsServiceInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\Status\DeleteStatus;
use Breeze\Util\Validate\Validations\Status\PostStatus;
use Breeze\Util\Validate\Validations\Status\StatusByProfile;
use Breeze\Util\Validate\Validations\Status\ValidateStatus;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class PostStatusTest extends TestCase
{
	private StatusRepositoryInterface | MockObject $repository;

	private PermissionsServiceInterface | MockObject $permissionsService;

	private User | MockObject $validateUser;

	private Allow | MockObject $validateAllow;

	private PostStatus $postStatus;

	/**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		$this->repository = $this->createMock(StatusRepositoryInterface::class);
		$this->permissionsService = $this->createMock(PermissionsServiceInterface::class);
		$this->validateUser = $this->createMock(User::class);
		$this->validateAllow = $this->createMock(Allow::class);

		$this->postStatus = new PostStatus(
			$this->createStub(Data::class),
			$this->validateUser,
			$this->validateAllow,
			$this->repository,
			$this->permissionsService
		);
	}

	public function testGetParams(): void
	{
		$this->assertEquals([
			'wall_id' => 0,
			'user_id' => 0,
			'body' => '',
		], $this->postStatus->getParams());
	}

	#[DataProvider('checkAllowProvider')]
	public function testCheckAllow(array $data, bool $canPost, bool $isExpectedException): void
	{
		$this->postStatus->setData($data);

		$this->permissionsService->expects($this->once())
			->method('canPost')
			->with(PermissionsEnum::TYPE_STATUS, $data[StatusEntity::WALL_ID])
			->willReturn($canPost);

		if ($isExpectedException) {
			// Flood control must not run once authorization fails.
			$this->validateAllow->expects($this->never())->method('floodControl');

			$this->expectException(NotAllowedException::class);
		} else {
			$this->validateAllow->expects($this->once())
				->method('floodControl')
				->with($data[StatusEntity::WALL_ID]);
		}

		$this->postStatus->checkAllow();
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
				'canPost' => true,
				'isExpectedException' => false,
			],
			'non-owner with post permission' => [
				'data' => [
					StatusEntity::WALL_ID => 2,
					StatusEntity::USER_ID => 1,
					StatusEntity::BODY => 'Test',
				],
				'canPost' => true,
				'isExpectedException' => false,
			],
			'non-owner without post permission' => [
				'data' => [
					StatusEntity::WALL_ID => 2,
					StatusEntity::USER_ID => 1,
					StatusEntity::BODY => 'Test',
				],
				'canPost' => false,
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * Regression: posting must always assert the poster is the session user,
	 * otherwise any member can attribute a status to someone else.
	 */
	public function testCheckUserAssertsPosterIsSessionUser(): void
	{
		$this->postStatus->setData([
			StatusEntity::WALL_ID => 2,
			StatusEntity::USER_ID => 1,
			StatusEntity::BODY => 'Test',
		]);

		$this->validateUser->expects($this->once())
			->method('isSameUser')
			->with(1);

		$this->validateUser->expects($this->once())
			->method('areValidUsers')
			->with([1, 2]);

		$this->postStatus->checkUser();
	}

	public function testCheckUserRejectsSpoofedPoster(): void
	{
		$this->postStatus->setData([
			StatusEntity::WALL_ID => 2,
			StatusEntity::USER_ID => 999,
			StatusEntity::BODY => 'Test',
		]);

		$this->validateUser->expects($this->once())
			->method('isSameUser')
			->with(999)
			->willThrowException(new DataNotFoundException('invalid_users'));

		// Existence checks must never run for a spoofed poster.
		$this->validateUser->expects($this->never())->method('areValidUsers');

		$this->expectException(DataNotFoundException::class);

		$this->postStatus->checkUser();
	}

	public function testSetValidatorWithUnknownActionDoesNotSetValidator(): void
	{
		$validateData = $this->createStub(Data::class);
		$validateUser = $this->createStub(User::class);
		$validateAllow = $this->createStub(Allow::class);
		$permissionsService = $this->createStub(PermissionsServiceInterface::class);

		$validateStatus = new ValidateStatus(
			new DeleteStatus($validateData, $validateUser, $validateAllow, $this->repository, $permissionsService),
			new PostStatus($validateData, $validateUser, $validateAllow, $this->repository, $permissionsService),
			new StatusByProfile($validateData, $validateUser, $validateAllow, $this->repository)
		);

		$validateStatus->setUp(['some' => 'data'], 'nonExistentAction');

		$this->assertNull($validateStatus->validator);
	}
}
