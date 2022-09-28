<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\InvalidStatusException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\ValidateGateway;
use Breeze\Util\Validate\ValidateGatewayInterface;
use Breeze\Util\Validate\Validations\Status\ValidateStatus;

class StatusController extends ApiBaseController implements ApiBaseInterface
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
		private StatusRepositoryInterface $statusRepository,
		protected ValidateGatewayInterface $gateway
	) {
		parent::__construct($gateway);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function setValidator(): void
	{
		$validatorName = ValidateStatus::getNameSpace() . ucfirst($this->subAction);

		$this->validator = new $validatorName(
			$this->statusRepository,
		);
	}

	public function statusByProfile(): void
	{
		$start = $this->getRequest('start', 0);
		$data = $this->validator->getData();

		try {
			$statusByProfile = $this->statusRepository->getByProfile($data[StatusEntity::WALL_ID], $start);

			$this->print($statusByProfile);
		} catch (InvalidStatusException $invalidStatusException) {
			$this->print([
				'type' => ValidateGateway::ERROR_TYPE,
				'message' => $this->getText($invalidStatusException->getMessage()),
			]);
		}
	}

	public function deleteStatus(): void
	{
		$data = $this->validator->getData();

		try {
			$this->statusRepository->deleteById($data[StatusEntity::ID]);

			$this->print($this->gateway->response());
		} catch (InvalidStatusException $invalidStatusException) {
			$this->print([
				'type' => ValidateGateway::ERROR_TYPE,
				'message' => $invalidStatusException->getMessage(),
			], 404);
		}
	}

	public function postStatus(): void
	{
		try {
			$statusId = $this->statusRepository->save($this->validator->getData());

			$this->print(array_merge(
				$this->gateway->response(),
				['content' => $this->statusRepository->getById($statusId)]
			));
		} catch (InvalidStatusException $invalidStatusException) {
			$this->print([
				'type' => ValidateGateway::ERROR_TYPE,
				'message' => $invalidStatusException->getMessage(),
			]);
		}
	}

	public function getMainAction(): string
	{
		return self::ACTION_PROFILE;
	}
}
