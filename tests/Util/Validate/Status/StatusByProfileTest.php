<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Status;

use Breeze\Controller\API\StatusController;
use Breeze\Entity\StatusEntity;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\InvalidDataException;
use Breeze\Util\Validate\Validations\Status\DeleteStatus;
use Breeze\Util\Validate\Validations\Status\PostStatus;
use Breeze\Util\Validate\Validations\Status\StatusByProfile;
use Breeze\Util\Validate\Validations\Status\ValidateStatus;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class StatusByProfileTest extends TestCase
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

		$statusByProfile = new StatusByProfile(
			$validateData,
			$validateUser,
			$validateAllow,
			$repository
		);

		$this->assertEquals([
			'wall_id' => 0,
		], $statusByProfile->getParams());
	}

	public function testEveryStatusSubActionIsExplicitlyMapped(): void
	{
		$mapped = $this->createValidateStatus()->validators();

		foreach (StatusController::SUB_ACTIONS as $subAction) {
			$this->assertArrayHasKey(
				$subAction,
				$mapped,
				sprintf('Sub-action "%s" has no entry in the validator map.', $subAction)
			);
		}

		$this->assertEquals(
			StatusController::SUB_ACTIONS,
			array_values(array_intersect(StatusController::SUB_ACTIONS, array_keys($mapped))),
			'The validator map drifted out of sync with StatusController::SUB_ACTIONS.'
		);
	}

	/**
	 * @throws Exception
	 */
	#[DataProvider('boundValidatorProvider')]
	public function testSubActionBindsExpectedValidator(string $subAction, string $expectedValidator): void
	{
		$validateStatus = $this->createValidateStatus();
		$validateStatus->setUp([StatusEntity::WALL_ID => 1], $subAction);

		$this->assertInstanceOf($expectedValidator, $validateStatus->validator);
	}

	/**
	 * @throws Exception
	 */
	public static function boundValidatorProvider(): array
	{
		return [
			'profile is bound to StatusByProfile' => ['profile', StatusByProfile::class],
			'total is bound to StatusByProfile' => ['total', StatusByProfile::class],
			'postStatus is bound to PostStatus' => ['postStatus', PostStatus::class],
			'deleteStatus is bound to DeleteStatus' => ['deleteStatus', DeleteStatus::class],
		];
	}

	/**
	 * @throws Exception
	 */
	#[DataProvider('boundValidatorProvider')]
	public function testIsValidDelegatesToBoundValidator(string $subAction, string $expectedValidator): void
	{
		$mocks = [
			StatusByProfile::class => $this->createMock(StatusByProfile::class),
			PostStatus::class => $this->createMock(PostStatus::class),
			DeleteStatus::class => $this->createMock(DeleteStatus::class),
		];

		foreach ($mocks as $class => $mock) {
			if ($class === $expectedValidator) {
				$mock->expects($this->once())->method('isValid');
			} else {
				$mock->expects($this->never())->method('isValid');
			}
		}

		$validateStatus = new ValidateStatus(
			$mocks[DeleteStatus::class],
			$mocks[PostStatus::class],
			$mocks[StatusByProfile::class]
		);

		$validateStatus->setUp([StatusEntity::WALL_ID => 1], $subAction);
		$validateStatus->isValid();
	}

	/**
	 * @throws Exception
	 */
	#[DataProvider('noOpSubActionProvider')]
	public function testSubActionsWithoutPayloadAreExplicitNoOps(string $subAction): void
	{
		$validateStatus = $this->createValidateStatus();
		$validateStatus->setUp([], $subAction);

		$this->assertArrayHasKey($subAction, $validateStatus->validators());
		$this->assertNull($validateStatus->validator);

		$validateStatus->isValid();
	}

	public static function noOpSubActionProvider(): array
	{
		return [
			'wall needs no validation' => ['wall'],
			'single needs no validation' => ['single'],
		];
	}

	/**
	 * @throws Exception
	 */
	public function testProfileValidationRejectsMissingWallId(): void
	{
		$validateStatus = $this->createValidateStatus();
		$validateStatus->setUp([], 'profile');

		$this->expectException(InvalidDataException::class);

		$validateStatus->isValid();
	}

	/**
	 * @throws Exception
	 */
	public function testProfileValidationAcceptsWallIdAndReceivesData(): void
	{
		$validateStatus = $this->createValidateStatus();
		$data = [StatusEntity::WALL_ID => 5];

		$validateStatus->setUp($data, 'profile');
		$validateStatus->isValid();

		$this->assertInstanceOf(StatusByProfile::class, $validateStatus->validator);
		$this->assertSame($data, $validateStatus->validator->data);
	}

	/**
	 * @throws Exception
	 */
	public function testUnknownSubActionBindsNoValidator(): void
	{
		$validateStatus = $this->createValidateStatus();
		$validateStatus->setUp(['some' => 'data'], 'nonExistentAction');

		$this->assertNull($validateStatus->validator);
	}

	/**
	 * @throws Exception
	 */
	private function createValidateStatus(): ValidateStatus
	{
		$repository = $this->createStub(StatusRepositoryInterface::class);
		$validateAllow = $this->createStub(Allow::class);
		$validateUser = $this->createStub(User::class);
		$validateData = new Data();

		return new ValidateStatus(
			new DeleteStatus($validateData, $validateUser, $validateAllow, $repository),
			new PostStatus($validateData, $validateUser, $validateAllow, $repository),
			new StatusByProfile($validateData, $validateUser, $validateAllow, $repository)
		);
	}
}
