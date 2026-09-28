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
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class DeleteStatusTest extends TestCase
{
	private StatusRepositoryInterface | MockObject $statusRepository;

	private PermissionsServiceInterface | MockObject $permissionsService;

	private User | MockObject $validateUser;

	private DeleteStatus $deleteStatus;

	/**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		$this->statusRepository = $this->createMock(StatusRepositoryInterface::class);
		$this->permissionsService = $this->createMock(PermissionsServiceInterface::class);
		$this->validateUser = $this->createMock(User::class);

		$this->deleteStatus = new DeleteStatus(
			$this->createStub(Data::class),
			$this->validateUser,
			$this->createStub(Allow::class),
			$this->statusRepository,
			$this->permissionsService
		);
	}

	public function testGetParams(): void
	{
		// user_id is intentionally absent: the author is always read from the
		// stored row, never from the request payload.
		$this->assertEquals([StatusEntity::ID => 0], $this->deleteStatus->getParams());
	}

	public function testSuccessKeyString(): void
	{
		$this->assertEquals('deleted_status', $this->deleteStatus->successKeyString());
	}

	#[DataProvider('checkAllowProvider')]
	public function testCheckAllow(
		int $storedAuthorId,
		int $storedWallId,
		bool $canDelete,
		bool $isExpectedException
	): void {
		$this->deleteStatus->setData([StatusEntity::ID => 1]);

		$this->statusRepository->method('getById')->willReturn(StatusEntity::from([
			StatusEntity::ID => 1,
			StatusEntity::USER_ID => $storedAuthorId,
			StatusEntity::WALL_ID => $storedWallId,
		]));

		$this->permissionsService->expects($this->once())
			->method('canDelete')
			->with(PermissionsEnum::TYPE_STATUS, $storedAuthorId, $storedWallId)
			->willReturn($canDelete);

		if ($isExpectedException) {
			$this->expectException(NotAllowedException::class);
		}

		$this->deleteStatus->checkAllow();

		if (!$isExpectedException) {
			$this->assertSame([StatusEntity::ID => 1], $this->deleteStatus->data);
		}
	}

	public static function checkAllowProvider(): array
	{
		return [
			'author deletes own status' => [
				'storedAuthorId' => 666,
				'storedWallId' => 1,
				'canDelete' => true,
				'isExpectedException' => false,
			],
			'wall owner deletes foreign status' => [
				'storedAuthorId' => 1,
				'storedWallId' => 666,
				'canDelete' => true,
				'isExpectedException' => false,
			],
			'moderator deletes any status' => [
				'storedAuthorId' => 1,
				'storedWallId' => 2,
				'canDelete' => true,
				'isExpectedException' => false,
			],
			'unauthorized member is denied' => [
				'storedAuthorId' => 1,
				'storedWallId' => 2,
				'canDelete' => false,
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * Regression: a spoofed user_id in the payload must not influence the
	 * authorization decision. The stored author (1) is used, not the
	 * client-supplied 666.
	 */
	public function testCheckAllowIgnoresClientSuppliedUserId(): void
	{
		$this->deleteStatus->setData([
			StatusEntity::ID => 1,
			StatusEntity::USER_ID => 666,
		]);

		$this->statusRepository->method('getById')->willReturn(StatusEntity::from([
			StatusEntity::ID => 1,
			StatusEntity::USER_ID => 1,
			StatusEntity::WALL_ID => 2,
		]));

		$this->permissionsService->expects($this->once())
			->method('canDelete')
			->with(PermissionsEnum::TYPE_STATUS, 1, 2)
			->willReturn(false);

		$this->expectException(NotAllowedException::class);

		$this->deleteStatus->checkAllow();
	}

	#[DataProvider('checkUserProvider')]
	public function testCheckUser(int $storedAuthorId, bool $isExpectedException): void
	{
		$this->deleteStatus->setData([StatusEntity::ID => 1]);

		$this->statusRepository->method('getById')->willReturn(StatusEntity::from([
			StatusEntity::ID => 1,
			StatusEntity::USER_ID => $storedAuthorId,
			StatusEntity::WALL_ID => 2,
		]));

		$this->validateUser->expects($this->once())
			->method('areValidUsers')
			->with([$storedAuthorId]);

		if ($isExpectedException) {
			$this->validateUser->method('areValidUsers')
				->willThrowException(new DataNotFoundException());

			$this->expectException(DataNotFoundException::class);
		}

		$this->deleteStatus->checkUser();
	}

	public static function checkUserProvider(): array
	{
		return [
			'validUsers' => [
				'storedAuthorId' => 666,
				'isExpectedException' => false,
			],
			'invalidUsers' => [
				'storedAuthorId' => 2,
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * checkUser() must validate the stored author, not the payload one.
	 */
	public function testCheckUserIgnoresClientSuppliedUserId(): void
	{
		$this->deleteStatus->setData([
			StatusEntity::ID => 1,
			StatusEntity::USER_ID => 666,
		]);

		$this->statusRepository->method('getById')->willReturn(StatusEntity::from([
			StatusEntity::ID => 1,
			StatusEntity::USER_ID => 1,
			StatusEntity::WALL_ID => 2,
		]));

		$this->validateUser->expects($this->once())
			->method('areValidUsers')
			->with([1]);

		$this->deleteStatus->checkUser();
	}

	public function testIsValidRunsDataAllowAndUserChecks(): void
	{
		$this->deleteStatus->setData([StatusEntity::ID => 1]);

		$this->statusRepository->method('getById')->willReturn(StatusEntity::from([
			StatusEntity::ID => 1,
			StatusEntity::USER_ID => 1,
			StatusEntity::WALL_ID => 2,
		]));

		$this->permissionsService->method('canDelete')->willReturn(true);

		$this->deleteStatus->isValid();

		$this->assertSame([StatusEntity::ID => 1], $this->deleteStatus->data);
	}

	public function testIsValidStopsOnNotAllowed(): void
	{
		$this->deleteStatus->setData([StatusEntity::ID => 1]);

		$this->statusRepository->method('getById')->willReturn(StatusEntity::from([
			StatusEntity::ID => 1,
			StatusEntity::USER_ID => 1,
			StatusEntity::WALL_ID => 2,
		]));

		$this->permissionsService->method('canDelete')->willReturn(false);

		// checkUser() must never run once authorization fails.
		$this->validateUser->expects($this->never())->method('areValidUsers');

		$this->expectException(NotAllowedException::class);

		$this->deleteStatus->isValid();
	}
}
