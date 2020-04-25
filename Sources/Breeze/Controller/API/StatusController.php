<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Service\StatusServiceInterface;
use Breeze\Service\UserServiceInterface;

class StatusController extends ApiBaseController implements ApiBaseInterface
{
	public const ACTION_PROFILE = 'byProfile';
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

	public function __construct(StatusServiceInterface $statusService, UserServiceInterface $userService)
	{
		$this->statusService = $statusService;
		$this->userService = $userService;
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function dispatch(): void
	{
		// TODO: validate request
		$this->setWallOwnerId();

		$this->subActionCall();
	}

	public function byProfile(): void
	{
		$start = (int) $this->getRequest('start');

		$statusByProfile = $this->statusService->getByProfile($this->wallOwnerId, $start);

		$this->print($statusByProfile);
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
