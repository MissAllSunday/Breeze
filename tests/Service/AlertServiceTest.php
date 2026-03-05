<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\AlertEntity;
use Breeze\Event\HandlerServiceProvider;
use Breeze\Repository\AlertRepositoryInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AlertServiceTest extends TestCase
{
	private AlertRepositoryInterface | MockObject $alertRepository;

	private AlertService $alertService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->alertRepository = $this->createMock(AlertRepositoryInterface::class);
		$handlerServiceProvider = $this->createStub(HandlerServiceProvider::class);
		$this->alertService = new AlertService($this->alertRepository, $handlerServiceProvider);
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
}
