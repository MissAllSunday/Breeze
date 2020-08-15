<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\InvalidStatusException;
use Breeze\Service\StatusServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Validate\ValidateGateway;
use Breeze\Util\Validate\ValidateGatewayInterface;
use Breeze\Util\Validate\Validations\ValidateData;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class StatusController extends ApiBaseController implements ApiBaseInterface
{
	public const ACTION_PROFILE = 'statusByProfile';
	public const ACTION_DELETE = 'deleteStatus';
	public const ACTION_POST = 'postStatus';
	public const SUB_ACTIONS = [
		self::ACTION_PROFILE,
	];

	/**
	 */
	private StatusServiceInterface $statusService;

	/**
	 */
	private UserServiceInterface $userService;

	public function __construct(
		StatusServiceInterface $statusService,
		UserServiceInterface $userService,
		ValidateGatewayInterface $gateway
	)
	{
		$this->statusService = $statusService;
		$this->userService = $userService;

		parent::__construct($gateway);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function setValidator(): ValidateDataInterface
	{
		$validatorName = ValidateData::getNameSpace() . ucfirst($this->subAction);

		return new $validatorName(
			$this->userService,
			$this->statusService,
		);
	}

	public function statusByProfile(): void
	{
		$start = (int) $this->getRequest('start');
		$data = $this->gateway->getData();

		try {
			$statusByProfile = $this->statusService->getByProfile($data[StatusEntity::COLUMN_OWNER_ID], $start);

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

	}

	public function postStatus(): void
	{
		$this->print(array_merge(
			$this->gateway->response(),
			['content' => $this->statusService->saveAndGet($this->gateway->getData())]
		));
	}

	public function render(string $subTemplate, array $params): void {}

	public function getMainAction(): string
	{
		return self::ACTION_PROFILE;
	}
}
