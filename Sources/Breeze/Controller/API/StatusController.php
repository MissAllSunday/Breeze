<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Service\StatusServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Validate\ValidateGatewayInterface;
use Breeze\Util\Validate\Validations\ValidateData;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class StatusController extends ApiBaseController implements ApiBaseInterface
{
	public const ACTION_PROFILE = 'statusByProfile';
	public const ACTION_DELETE = 'deleteStatus';
	public const SUB_ACTIONS = [
		self::ACTION_PROFILE,
	];

	/**
	 * @var StatusServiceInterface
	 */
	private $statusService;

	/**
	 * @var UserServiceInterface
	 */
	private $userService;

	/**
	 * @var int
	 */
	private $wallOwnerId;

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

		$statusByProfile = $this->statusService->getByProfile($this->wallOwnerId, $start);

		$this->print($statusByProfile);
	}

	public function deleteStatus(): void
	{

	}

	public function render(string $subTemplate, array $params): void {}

	public function getMainAction(): string
	{
		return self::ACTION_PROFILE;
	}

	private function setWallOwnerId(): int
	{
		return $this->wallOwnerId = $this->getRequest('u', 0);
	}
}
