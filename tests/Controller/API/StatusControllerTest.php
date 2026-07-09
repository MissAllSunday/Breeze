<?php

declare(strict_types=1);

namespace Breeze\Controller\API;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\InvalidStatusException;
use Breeze\Service\StatusServiceInterface;
use Breeze\Util\Response;
use Breeze\Util\Validate\DataNotFoundException;
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

	private Response | MockObject $response;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->statusService = $this->createMock(StatusServiceInterface::class);
		$validateActions = $this->createMock(ValidateActionsInterface::class);
		$this->response = $this->createMock(Response::class);

		$this->statusController = new StatusController(
			$this->statusService,
			$validateActions,
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
		$expectedData = [
			'data' => [['id' => 1, 'body' => 'Test status']], // Corrected key from 'statuses' to 'data'
			'permissions' => ['delete' => true, 'post' => true],
			'pagination' => ['nextCursor' => null, 'hasMore' => false],
			'total' => 1,
		];

		// Set up the data property via reflection since it's set in dispatch()
		$reflection = new \ReflectionClass($this->statusController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setAccessible(true);
		$dataProperty->setValue($this->statusController, [StatusEntity::WALL_ID => $wallId]);

		$this->statusService->expects($this->once())
			->method('getByProfile')
			->with($wallId, null)
			->willReturn($expectedData);

		$this->response->expects($this->once())
			->method('success')
			->with('', $expectedData);

		$this->statusController->profile();
	}

	public function testProfileReturnsEmptyDataWhenNoStatusesFound(): void
	{
		$wallId = 123;
		$expectedEmptyData = [
			'data' => [], // Corrected key from 'statuses' to 'data'
			'permissions' => ['delete' => true, 'post' => true], // Added permissions to match service return
			'pagination' => ['nextCursor' => null, 'hasMore' => false], // Added pagination to match service return
			'total' => 0,
		];

		$reflection = new \ReflectionClass($this->statusController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setValue($this->statusController, [StatusEntity::WALL_ID => $wallId]);

		$this->statusService->expects($this->once())
			->method('getByProfile')
			->willReturn($expectedEmptyData);

		$this->statusService->expects($this->once())
			->method('currentUserInfo')
			->willReturn(['id' => 456]);

		$this->response->expects($this->once())
			->method('success')
			->with('empty_data_other_wall', $expectedEmptyData);

		$this->statusController->profile();
	}

	public function testWallSuccess(): void
	{
		$expectedData = [
			'data' => [['id' => 1, 'body' => 'Buddy status']], // Corrected key from 'statuses' to 'data'
			'permissions' => ['delete' => true, 'post' => true], // Added permissions to match service return
			'pagination' => ['nextCursor' => null, 'hasMore' => false], // Added pagination to match service return
			'total' => 1,
		];

		$this->statusService->expects($this->once())
			->method('getByBuddies')
			->with(null)
			->willReturn($expectedData);

		$this->response->expects($this->once())
			->method('success')
			->with('', $expectedData);

		$this->statusController->wall();
	}

	public function testWallReturnsEmptyDataWhenNoBuddiesStatusesFound(): void
	{
		$expectedEmptyData = [
			'data' => [], // Corrected key from 'statuses' to 'data'
			'permissions' => ['delete' => true, 'post' => true], // Added permissions to match service return
			'pagination' => ['nextCursor' => null, 'hasMore' => false], // Added pagination to match service return
			'total' => 0,
		];

		$this->statusService->expects($this->once())
			->method('getByBuddies')
			->willReturn($expectedEmptyData);

		$this->response->expects($this->once())
			->method('success')
			->with('', $expectedEmptyData);

		$this->statusController->wall();
	}

	public function testDeleteStatusSuccess(): void
	{
		$statusId = 42;

		$reflection = new \ReflectionClass($this->statusController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setValue($this->statusController, [StatusEntity::ID => $statusId]);

		$this->statusService->expects($this->once())
			->method('deleteById')
			->with($statusId);

		$this->response->expects($this->once())
			->method('success')
			->with('deleted_status', [], Response::OK);

		$this->statusController->deleteStatus();
	}

	public function testDeleteStatusThrowsInvalidStatusException(): void
	{
		$statusId = 999;
		$errorMessage = 'Status not found';

		$reflection = new \ReflectionClass($this->statusController);
		$dataProperty = $reflection->getProperty('data');
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

	public function testSingleSuccess(): void
	{
		$statusId = 123;
		$expectedData = [
			'data' => [StatusEntity::from(['id' => $statusId, 'body' => 'Test status'])],
			'permissions' => ['delete' => true, 'post' => true],
			'pagination' => ['nextCursor' => null, 'hasMore' => false],
			'total' => 1,
		];

		// Set the id in the request
		$_REQUEST['id'] = $statusId;

		$this->statusService->expects($this->once())
			->method('getById')
			->with($statusId)
			->willReturn($expectedData);

		$this->response->expects($this->once())
			->method('success')
			->with('', $expectedData);

		$this->statusController->single();

		// Clean up
		unset($_REQUEST['id']);
	}

	public function testSingleWithMissingId(): void
	{
		$this->response->expects($this->once())
			->method('error')
			->with('error_no_status', Response::BAD_REQUEST);

		$this->statusController->single();
	}

	public function testSingleWithZeroId(): void
	{
		$_REQUEST['id'] = 0;

		$this->response->expects($this->once())
			->method('error')
			->with('error_no_status', Response::BAD_REQUEST);

		$this->statusController->single();

		// Clean up
		unset($_REQUEST['id']);
	}

	public function testSingleThrowsDataNotFoundException(): void
	{
		$statusId = 999;
		$errorMessage = 'error_no_status';

		$_REQUEST['id'] = $statusId;

		$this->statusService->expects($this->once())
			->method('getById')
			->with($statusId)
			->willThrowException(new DataNotFoundException($errorMessage));

		$this->response->expects($this->once())
			->method('error')
			->with($errorMessage);

		$this->statusController->single();

		// Clean up
		unset($_REQUEST['id']);
	}

	public function testTotalSuccess(): void
	{
		$wallId = 456;
		$expectedData = [
			'data' => [['id' => 1]], // Corrected key from 'statuses' to 'data'
			'permissions' => ['delete' => true, 'post' => true], // Added permissions to match service return
			'pagination' => ['nextCursor' => null, 'hasMore' => false], // Added pagination to match service return
			'total' => 10,
		];

		$reflection = new \ReflectionClass($this->statusController);
		$dataProperty = $reflection->getProperty('data');
		$dataProperty->setAccessible(true);
		$dataProperty->setValue($this->statusController, [StatusEntity::WALL_ID => $wallId]);

		$this->statusService->expects($this->once())
			->method('getByProfile')
			->with($wallId, null)
			->willReturn($expectedData);

		$this->response->expects($this->once())
			->method('success')
			->with('', $expectedData);

		$this->statusController->total();
	}

	public function testTotalThrowsInvalidStatusException(): void
	{
		$wallId = 456;
		$errorMessage = 'error_generic';

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
