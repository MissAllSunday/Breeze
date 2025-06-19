<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\StatusEntity;
use Breeze\Event\EventServiceProvider;
use Breeze\Event\Status\StatusCreatedEvent;
use Breeze\Repository\InvalidStatusException;
use Breeze\Service\StatusServiceInterface;
use Breeze\Util\Response;
use Breeze\Util\Validate\EmptyDataException;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;

class StatusController extends ApiBaseController
{
	public const string ACTION_PROFILE = 'profile';
	public const string ACTION_GENERAL = 'general';
	public const string ACTION_DELETE = 'deleteStatus';
	public const string ACTION_POST = 'postStatus';
	public const string ACTION_TOTAL = 'total';

	public const string ACTION_SINGLE = 'single';

	public const array SUB_ACTIONS = [
		self::ACTION_PROFILE,
		self::ACTION_POST,
		self::ACTION_DELETE,
		self::ACTION_GENERAL,
		self::ACTION_TOTAL,
	];

	public function __construct(
		protected StatusServiceInterface $statusService,
		protected ValidateActionsInterface $validateActions,
		protected Response $response,
		protected EventServiceProvider $eventServiceProvider
	) {
		parent::__construct($validateActions, $response, $eventServiceProvider);
	}

	public function profile(): void
	{
		try {
			$statusByProfile = $this->statusService->getByProfile(
				$this->data[StatusEntity::WALL_ID],
				$this->getRequest('start', 0)
			);

			$this->response->success('', $statusByProfile);
		} catch (EmptyDataException $emptyDataException) {
			$this->response->error($emptyDataException->getMessage());
		}
	}

	public function general(): void
	{
		try {
			$buddiesStatus = $this->statusService->getByBuddies($this->getRequest('start', 0));

			if ($buddiesStatus === []) {
				$this->response->success('', []);
			}

			$this->response->success('', $buddiesStatus);
		} catch (InvalidStatusException | EmptyDataException $exception) {
			$this->response->error($exception->getMessage());
		}
	}

	public function deleteStatus(): void
	{
		try {
			$statusId = (int) $this->data[StatusEntity::ID];
			$this->statusService->deleteById($statusId);

			$this->response->success('deleted_status', [], Response::NO_CONTENT);
		} catch (InvalidStatusException $invalidStatusException) {
			$this->response->error($invalidStatusException->getMessage());
		}
	}

	public function postStatus(): void
	{
		try {
			$statusHandledEntity = $this->statusService->save($this->data);

			// Dispatch the status created event
			$this->eventDispatch(StatusCreatedEvent::class, $statusHandledEntity);

			$this->response->success(
				'published_status',
				$statusHandledEntity->toArray(),
				Response::CREATED
			);
		} catch (InvalidStatusException $invalidStatusException) {
			$this->response->error($invalidStatusException->getMessage());
		}
	}

	public function total(): void
	{
		try {
			$statusByProfile = $this->statusService->getByProfile(
				$this->data[StatusEntity::WALL_ID],
				$this->getRequest('start', 0)
			);

			$this->response->success('', $statusByProfile);
		} catch (InvalidStatusException $invalidStatusException) {
			$this->response->error($invalidStatusException->getMessage());
		}
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}
}
