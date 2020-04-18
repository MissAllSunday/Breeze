<?php

declare(strict_types=1);


namespace Breeze\Controller;

class StatusController extends BaseController implements ControllerInterface
{
	public const ACTION_PROFILE = 'byProfile';
	public const SUB_ACTIONS = [
		self::ACTION_PROFILE
	];

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
		$statusByProfile = $start = $this->getRequest('start', 0);

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
