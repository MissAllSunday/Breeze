<?php

declare(strict_types=1);


namespace Breeze\Controller;

class Status extends BaseController implements ControllerInterface
{
	const CREATE = 'create';
	const DELETE = 'delete';

	const ACTIONS = [
		self::CREATE,
		self::DELETE,
	];

	public function do(): void
	{

	}

	public function create(): void
	{
		// TODO: Implement create() method.
	}

	public function update(): void
	{
		// TODO: Implement update() method.
	}

	public function delete(): void
	{
		// TODO: Implement delete() method.
	}
}
