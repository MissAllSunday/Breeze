<?php

declare(strict_types=1);


namespace Breeze\Controller\User;

use Breeze\Controller\BaseController;
use Breeze\Controller\ControllerInterface;

class WallController extends BaseController implements ControllerInterface
{
	public const SUB_ACTIONS = [
		'main',
		'status',
		'comment',
		'userCard'
	];

	public function dispatch(): void
	{
		// Master setting is off, back off!
		if (!$this->service->enable('master'))
			fatal_lang_error('Breeze_error_no_valid_action', false);

		// Guest aren't allowed, sorry.
		is_not_guest($this->service->getText('error_no_access'));

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
