<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Repository\AlertRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
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
		$this->alertService = new AlertService($this->alertRepository);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function testGetById(): void
	{
		$alertId = 123;
		$expectedAlert = ['id' => 123, 'content_type' => 'notification'];

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
}
