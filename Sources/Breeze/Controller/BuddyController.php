<?php

declare(strict_types=1);


namespace Breeze\Controller;

class BuddyController extends BaseController implements ControllerInterface
{
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

	public function getActionName(): string
	{
		return '';
	}
}
