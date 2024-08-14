<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\InvalidStatusException;
use Breeze\Service\StatusService;
use Breeze\Util\Response;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;

class StatusController extends ApiBaseController
{
	public const ACTION_PROFILE = 'profile';
	public const ACTION_GENERAL = 'general';
	public const ACTION_DELETE = 'deleteStatus';
	public const ACTION_POST = 'postStatus';
	public const ACTION_TOTAL = 'total';

	public const SUB_ACTIONS = [
		self::ACTION_PROFILE,
		self::ACTION_POST,
		self::ACTION_DELETE,
		self::ACTION_GENERAL,
		self::ACTION_TOTAL,
	];

	public function __construct(
		protected StatusService $statusService,
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
		} catch (InvalidStatusException $invalidStatusException) {
			$this->response->error($invalidStatusException->getMessage());
		}
	}

	public function general(): void
	{
		try {
			$buddiesStatus = $this->statusService->getByBuddies($this->getRequest('start', 0));

			if (empty($buddiesStatus)) {
				$this->response->success('', []);
			}

			$this->response->success('', $buddiesStatus);
		} catch (InvalidStatusException $invalidStatusException) {
			$this->response->error($invalidStatusException->getMessage());
		}
	}

	public function deleteStatus(): void
	{
		try {
			$this->statusService->deleteById($this->data[StatusEntity::ID]);

			$this->response->success('deleted_status', [], Response::NO_CONTENT);
		} catch (InvalidStatusException $invalidStatusException) {
			$this->response->error($invalidStatusException->getMessage());
		}
	}

	public function postStatus(): void
	{
		try {
			$status = $this->statusService->save($this->data);

			$this->response->success(
				'published_status',
				$status,
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
