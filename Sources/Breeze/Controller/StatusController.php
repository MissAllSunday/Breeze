<?php

declare(strict_types=1);


namespace Breeze\Controller;

use Breeze\Service\StatusServiceInterface;
use Breeze\Service\UserServiceInterface;

class StatusController extends BaseController implements ControllerInterface
{
	public const ACTION_PROFILE = 'byProfile';
	public const SUB_ACTIONS = [
		self::ACTION_PROFILE
	];

	/**
	 * @var StatusServiceInterface
	 */
	private $statusService;

	/**
	 * @var UserServiceInterface
	 */
	private $userService;

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
		$this->subActionCall();
	}

	public function byProfile(): void
	{
		$start = (int) $this->getRequest('start');

		$statusByProfile = $this->statusService->getByProfile(1, $start);
	}

	public function render(string $subTemplate, array $params): void
	{
		// TODO: Implement render() method.
	}

	public function getMainAction(): string
	{
		return self::ACTION_PROFILE;
	}
}
