<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\AlertEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Event\HandlerServiceProvider;
use Breeze\Repository\AlertRepositoryInterface;
use Breeze\Repository\User\SettingsRepositoryInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class AlertServiceTest extends TestCase
{
	private AlertRepositoryInterface | MockObject $alertRepository;

	private SettingsRepositoryInterface | MockObject $userSettingsRepository;

	private AlertService $alertService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->alertRepository = $this->createMock(AlertRepositoryInterface::class);
		$this->userSettingsRepository = $this->createMock(SettingsRepositoryInterface::class);
		$handlerServiceProvider = $this->createStub(HandlerServiceProvider::class);
		$this->alertService = new AlertService(
			$this->alertRepository,
			$handlerServiceProvider,
			$this->userSettingsRepository,
		);
	}

	public function testGetById(): void
	{
		$alertId = 123;
		$expectedAlert = AlertEntity::from(['id_alert' => 123, 'content_type' => 'notification']);

		$this->alertRepository
			->expects($this->once())
			->method('getById')
			->with($this->equalTo($alertId))
			->willReturn($expectedAlert);

		$result = $this->alertService->getById($alertId);

		$this->assertEquals($expectedAlert, $result);
	}

	public function testDelete(): void
	{
		$alertId = 123;

		$this->alertRepository
			->expects($this->once())
			->method('delete')
			->with($this->equalTo([$alertId]))
			->willReturn(true);

		$result = $this->alertService->delete($alertId);

		$this->assertTrue($result);
	}

	public function testCheckAlertDelegatesToRepository(): void
	{
		$alertEntity = AlertEntity::from([
			AlertEntity::ID_MEMBER => 10,
			AlertEntity::CONTENT_TYPE => 'Breeze_like',
			AlertEntity::CONTENT_ID => 100,
			AlertEntity::ID_MEMBER_STARTED => 5,
		]);

		$this->alertRepository
			->expects($this->once())
			->method('checkAlert')
			->with($this->equalTo($alertEntity))
			->willReturn(true);

		$result = $this->alertService->checkAlert($alertEntity);

		$this->assertTrue($result);
	}

	public function testCheckAlertReturnsFalseWhenNoExistingAlert(): void
	{
		$alertEntity = AlertEntity::from([
			AlertEntity::ID_MEMBER => 10,
			AlertEntity::CONTENT_TYPE => 'Breeze_like',
			AlertEntity::CONTENT_ID => 100,
			AlertEntity::ID_MEMBER_STARTED => 5,
		]);

		$this->alertRepository
			->expects($this->once())
			->method('checkAlert')
			->with($this->equalTo($alertEntity))
			->willReturn(false);

		$result = $this->alertService->checkAlert($alertEntity);

		$this->assertFalse($result);
	}

	public function testSendSuppressesAlertWhenSenderBlocksRecipient(): void
	{
		$alert = AlertEntity::from([
			AlertEntity::ID_MEMBER => 2,
			AlertEntity::ID_MEMBER_STARTED => 1,
			AlertEntity::CONTENT_TYPE => 'Breeze_status',
		]);

		// Sender (1) has recipient (2) in their block list
		$this->userSettingsRepository->method('getByIds')->willReturn([
			1 => UserSettingsEntity::from([UserSettingsEntity::BLOCK_LIST => '2']),
			2 => UserSettingsEntity::from([]),
		]);

		$this->alertRepository->expects($this->never())->method('insert');

		$this->alertService->send($alert);
	}

	public function testSendSuppressesAlertWhenRecipientBlocksSender(): void
	{
		$alert = AlertEntity::from([
			AlertEntity::ID_MEMBER => 2,
			AlertEntity::ID_MEMBER_STARTED => 1,
			AlertEntity::CONTENT_TYPE => 'Breeze_status',
		]);

		// Recipient (2) has sender (1) in their block list
		$this->userSettingsRepository->method('getByIds')->willReturn([
			1 => UserSettingsEntity::from([]),
			2 => UserSettingsEntity::from([UserSettingsEntity::BLOCK_LIST => '1']),
		]);

		$this->alertRepository->expects($this->never())->method('insert');

		$this->alertService->send($alert);
	}

	public function testSendDeliversAlertWhenNoBlockRelationshipExists(): void
	{
		$alert = AlertEntity::from([
			AlertEntity::ID_MEMBER => 2,
			AlertEntity::ID_MEMBER_STARTED => 1,
			AlertEntity::CONTENT_TYPE => 'Breeze_status',
		]);

		$this->userSettingsRepository->method('getByIds')->willReturn([
			1 => UserSettingsEntity::from([]),
			2 => UserSettingsEntity::from([]),
		]);

		$this->alertRepository->method('checkAlert')->willReturn(false);
		$this->alertRepository->expects($this->once())->method('insert');

		$this->alertService->send($alert);
	}

	public function testSendSkipsBlockCheckWhenSenderIdIsZero(): void
	{
		$alert = AlertEntity::from([
			AlertEntity::ID_MEMBER => 2,
			AlertEntity::ID_MEMBER_STARTED => 0,
			AlertEntity::CONTENT_TYPE => 'Breeze_buddy',
		]);

		// getByIds must not be called for system alerts with no sender
		$this->userSettingsRepository->expects($this->never())->method('getByIds');
		$this->alertRepository->method('checkAlert')->willReturn(false);
		$this->alertRepository->expects($this->once())->method('insert');

		$this->alertService->send($alert);
	}
}
