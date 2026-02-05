<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\InvalidStatusException;
use Breeze\Service\StatusServiceInterface;
use Breeze\Util\Response;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\EmptyDataException;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;

class StatusController extends ApiBaseController
{
	public const string ACTION_PROFILE = 'profile';
	public const string ACTION_WALL = 'wall';
	public const string ACTION_DELETE = 'deleteStatus';
	public const string ACTION_POST = 'postStatus';
	public const string ACTION_TOTAL = 'total';

	public const string ACTION_SINGLE = 'single';

	public const array SUB_ACTIONS = [
		self::ACTION_PROFILE,
		self::ACTION_POST,
		self::ACTION_DELETE,
		self::ACTION_WALL,
		self::ACTION_TOTAL,
	];

	public function __construct(
		protected StatusServiceInterface $statusService,
		protected ValidateActionsInterface $validateActions,
		protected Response $response
	) {
		parent::__construct($validateActions, $response);
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

	public function wall(): void
	{
		try {
			$buddiesStatus = $this->statusService->getByBuddies($this->getRequest('start', 0));

			$this->response->success('', $buddiesStatus);
		} catch (EmptyDataException $exception) {
			$this->response->error($exception->getMessage());
		}
	}

	public function deleteStatus(): void
	{
		try {
			$statusId = (int) $this->data[StatusEntity::ID];
			$this->statusService->deleteById($statusId);

			$this->response->success('deleted_status', [], Response::NO_CONTENT);
		} catch (InvalidStatusException|DataNotFoundException $exception) {
			$this->response->error($exception->getMessage());
		}
	}

	public function postStatus(): void
	{
		try {
			$statusEntities = $this->statusService->save($this->data);

			$this->response->success(
				'published_status',
				$statusEntities,
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
