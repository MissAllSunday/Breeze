<?php

declare(strict_types=1);

namespace Breeze\Controller\API;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\InvalidStatusException;
use Breeze\Service\StatusServiceInterface;
use Breeze\Util\Response;
use Breeze\Util\Validate\EmptyDataException;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class StatusControllerTest extends TestCase
{
	private StatusController $statusController;

	private StatusServiceInterface | MockObject $statusService;

	private ValidateActionsInterface | MockObject $validateActions;

	private Response | MockObject $response;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->statusService = $this->createMock(StatusServiceInterface::class);
		$this->validateActions = $this->createMock(ValidateActionsInterface::class);
		$this->response = $this->createMock(Response::class);

		$this->statusController = new StatusController(
			$this->statusService,
			$this->validateActions,
			$this->response
		);
	}

	public function testGetSubActions(): void
	{
		$this->assertEquals(StatusController::SUB_ACTIONS, $this->statusController->getSubActions());
	}

	public function testProfileSuccess(): void
	{
		$wallId = 123;
		$start = 0;
		$expectedData = [
			'statuses' => [['id' => 1, 'body' => 'Test status']],
			'pagination' => ['total' => 1],
		];

		// Set up the data property via reflection since it's set in dispatch()
		$reflection = new \ReflectionClass($this->statusController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setAccessible(true);
		$dataProperty->setValue($this->statusController, [StatusEntity::WALL_ID => $wallId]);

		$this->statusService->expects($this->once())
			->method('getByProfile')
			->with($wallId, $start)
			->willReturn($expectedData);

		$this->response->expects($this->once())
			->method('success')
			->with('', $expectedData);

		$this->statusController->profile();
	}

	public function testProfileThrowsEmptyDataException(): void
	{
		$wallId = 123;
		$errorMessage = 'No statuses found';

		$reflection = new \ReflectionClass($this->statusController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setAccessible(true);
		$dataProperty->setValue($this->statusController, [StatusEntity::WALL_ID => $wallId]);

		$this->statusService->expects($this->once())
			->method('getByProfile')
			->willThrowException(new EmptyDataException($errorMessage));

		$this->response->expects($this->once())
			->method('error')
			->with($errorMessage);

		$this->statusController->profile();
	}

	public function testWallSuccess(): void
	{
		$start = 0;
		$expectedData = [
			'statuses' => [['id' => 1, 'body' => 'Buddy status']],
		];

		$this->statusService->expects($this->once())
			->method('getByBuddies')
			->with($start)
			->willReturn($expectedData);

		$this->response->expects($this->once())
			->method('success')
			->with('', $expectedData);

		$this->statusController->wall();
	}

	public function testWallThrowsEmptyDataException(): void
	{
		$errorMessage = 'No buddy statuses found';

		$this->statusService->expects($this->once())
			->method('getByBuddies')
			->willThrowException(new EmptyDataException($errorMessage));

		$this->response->expects($this->once())
			->method('error')
			->with($errorMessage);

		$this->statusController->wall();
	}

	public function testDeleteStatusSuccess(): void
	{
		$statusId = 42;

		$reflection = new \ReflectionClass($this->statusController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setAccessible(true);
		$dataProperty->setValue($this->statusController, [StatusEntity::ID => $statusId]);

		$this->statusService->expects($this->once())
			->method('deleteById')
			->with($statusId);

		$this->response->expects($this->once())
			->method('success')
			->with('deleted_status', [], Response::NO_CONTENT);

		$this->statusController->deleteStatus();
	}

	public function testDeleteStatusThrowsInvalidStatusException(): void
	{
		$statusId = 999;
		$errorMessage = 'Status not found';

		$reflection = new \ReflectionClass($this->statusController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setAccessible(true);
		$dataProperty->setValue($this->statusController, [StatusEntity::ID => $statusId]);

		$this->statusService->expects($this->once())
			->method('deleteById')
			->willThrowException(new InvalidStatusException($errorMessage));

		$this->response->expects($this->once())
			->method('error')
			->with($errorMessage);

		$this->statusController->deleteStatus();
	}

	public function testPostStatusSuccess(): void
	{
		$statusData = [
			StatusEntity::WALL_ID => 123,
			StatusEntity::BODY => 'New status message',
		];
		$expectedEntities = [
			StatusEntity::from([
				StatusEntity::ID => 1,
				StatusEntity::WALL_ID => 123,
				StatusEntity::BODY => 'New status message',
			]),
		];

		$reflection = new \ReflectionClass($this->statusController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setAccessible(true);
		$dataProperty->setValue($this->statusController, $statusData);

		$this->statusService->expects($this->once())
			->method('save')
			->with($statusData)
			->willReturn($expectedEntities);

		$this->response->expects($this->once())
			->method('success')
			->with('published_status', $expectedEntities, Response::CREATED);

		$this->statusController->postStatus();
	}

	public function testPostStatusThrowsInvalidStatusException(): void
	{
		$statusData = [
			StatusEntity::WALL_ID => 123,
			StatusEntity::BODY => '',
		];
		$errorMessage = 'Invalid status data';

		$reflection = new \ReflectionClass($this->statusController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setAccessible(true);
		$dataProperty->setValue($this->statusController, $statusData);

		$this->statusService->expects($this->once())
			->method('save')
			->willThrowException(new InvalidStatusException($errorMessage));

		$this->response->expects($this->once())
			->method('error')
			->with($errorMessage);

		$this->statusController->postStatus();
	}

	public function testTotalSuccess(): void
	{
		$wallId = 456;
		$expectedData = [
			'statuses' => [['id' => 1]],
			'total' => 10,
		];

		$reflection = new \ReflectionClass($this->statusController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setAccessible(true);
		$dataProperty->setValue($this->statusController, [StatusEntity::WALL_ID => $wallId]);

		$this->statusService->expects($this->once())
			->method('getByProfile')
			->with($wallId, 0)
			->willReturn($expectedData);

		$this->response->expects($this->once())
			->method('success')
			->with('', $expectedData);

		$this->statusController->total();
	}

	public function testTotalThrowsInvalidStatusException(): void
	{
		$wallId = 456;
		$errorMessage = 'Error getting total';

		$reflection = new \ReflectionClass($this->statusController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setAccessible(true);
		$dataProperty->setValue($this->statusController, [StatusEntity::WALL_ID => $wallId]);

		$this->statusService->expects($this->once())
			->method('getByProfile')
			->willThrowException(new InvalidStatusException($errorMessage));

		$this->response->expects($this->once())
			->method('error')
			->with($errorMessage);

		$this->statusController->total();
	}
}
