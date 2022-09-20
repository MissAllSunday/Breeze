<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\StatusEntity;
use Breeze\Exceptions\InvalidStatusException;
use Breeze\Service\StatusServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Validate\ValidateGateway;
use Breeze\Util\Validate\ValidateGatewayInterface;
use Breeze\Util\Validate\Validations\Status\ValidateStatus;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

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

	protected ValidateDataInterface $validator;

	public function __construct(
		private StatusServiceInterface $statusService,
		private UserServiceInterface $userService,
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
			$this->userService,
			$this->statusService,
		);
	}

	public function getValidator(): ValidateDataInterface
	{
		return $this->validator;
	}

	public function statusByProfile(): void
	{
		$start = $this->getRequest('start');
		$data = $this->validator->getData();

		try {
			// @TODO: should be done by the repository
			$statusByProfile = $this->statusService->getByProfile($data[StatusEntity::WALL_ID], $start);

			$this->print($statusByProfile);
		} catch (InvalidStatusException $e) {
			$this->print([
				'type' => ValidateGateway::ERROR_TYPE,
				'message' => $this->getText($e->getMessage()),
			]);
		}
	}

	public function deleteStatus(): void
	{
		$data = $this->validator->getData();

		try {
			$this->statusService->deleteById($data[StatusEntity::ID]);

			$this->print($this->gateway->response());
		} catch (InvalidStatusException $e) {
			$this->print([
				'type' => ValidateGateway::ERROR_TYPE,
				'message' => $e->getMessage(),
			]);
		}
	}

	public function postStatus(): void
	{
		$this->print(array_merge(
			$this->gateway->response(),
			['content' => $this->statusService->saveAndGet($this->validator->getData())]
		));
	}

	public function render(string $subTemplate, array $params): void
	{
	}

	public function getMainAction(): string
	{
		return self::ACTION_PROFILE;
	}
}
