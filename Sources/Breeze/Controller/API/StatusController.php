<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\InvalidStatusException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Response;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class StatusController extends ApiBaseController
{
	public const ACTION_PROFILE = 'statusByProfile';
	public const ACTION_DELETE = 'deleteStatus';
	public const ACTION_POST = 'postStatus';

	public const SUB_ACTIONS = [
		self::ACTION_PROFILE,
		self::ACTION_POST,
		self::ACTION_DELETE,
	];

	public function __construct(
		protected StatusRepositoryInterface $statusRepository,
		protected ValidateDataInterface $validator,
		protected Response $response
	) {
		parent::__construct($validator, $response);
	}

	public function statusByProfile(): void
	{
		try {
			$statusByProfile = $this->statusRepository->getByProfile(
				$this->data[StatusEntity::WALL_ID],
				$this->getRequest('start', 0)
			);

			$this->response->success($this->validator->successKeyString(), $statusByProfile);
		} catch (InvalidStatusException $invalidStatusException) {
			$this->response->error($invalidStatusException->getMessage());
		}
	}

	public function deleteStatus(): void
	{
		try {
			$this->statusRepository->deleteById($this->data[StatusEntity::ID]);

			$this->response->success($this->validator->successKeyString(), [], Response::NO_CONTENT);
		} catch (InvalidStatusException $invalidStatusException) {
			$this->response->error($invalidStatusException->getMessage());
		}
	}

	public function postStatus(): void
	{
		try {
			$statusId = $this->statusRepository->save($this->data);

			$this->response->success(
				$this->validator->successKeyString(),
				$this->statusRepository->getById($statusId),
				Response::CREATED
			);
		} catch (InvalidStatusException $invalidStatusException) {
			$this->response->error($invalidStatusException->getMessage());
		}
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}
}
