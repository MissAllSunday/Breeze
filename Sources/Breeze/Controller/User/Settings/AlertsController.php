<?php

declare(strict_types=1);


namespace Breeze\Controller\User\Settings;

use Breeze\Controller\BaseController;
use Breeze\Controller\ControllerInterface as ControllerInterfaceAlias;

class AlertsController extends BaseController implements ControllerInterfaceAlias
{
	public function __construct()
	{
	}

	public function getSubActions(): array
	{
		return [];
	}

	public function dispatch(): void
	{
		// TODO: Implement dispatch() method.
	}

	public function main(): void
	{
		// TODO: Implement main() method.
	}

	public function render(string $subTemplate, array $params): void
	{
		// TODO: Implement render() method.
	}

	public function getMainAction(): string
	{
		return '';
	}
}
