<?php

declare(strict_types=1);


namespace Breeze\Controller\User;

use Breeze\Controller\BaseController;
use Breeze\Controller\ControllerInterface;

class WallController extends BaseController implements ControllerInterface
{
	public const SUB_ACTIONS = [
		'main',
	];

	public function dispatch(): void
	{
		$this->subActionCall();
	}

	public function main(): void
	{
		// TODO: Implement main() method.
	}

	public function render(string $subTemplate, array $params): void
	{
		// TODO: Implement render() method.
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}
}
