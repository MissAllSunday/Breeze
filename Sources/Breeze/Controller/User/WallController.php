<?php

declare(strict_types=1);


namespace Breeze\Controller\User;

use Breeze\Breeze;
use Breeze\Controller\BaseController;
use Breeze\Controller\ControllerInterface;

class WallController extends BaseController implements ControllerInterface
{
	public const SUB_ACTIONS = [
		'main',
		'status',
		'comment',
	];

	public function dispatch(): void
	{
		if (!$this->service->enable('master'))
			fatal_lang_error('Breeze_error_no_valid_action', false);

		is_not_guest($this->service->getText('error_no_access'));

		$this->service->setLanguage(Breeze::NAME);
		$this->service->setTemplate(Breeze::NAME);

		$this->subActionCall();
	}

	public function main(): void
	{
		$this->render(__FUNCTION__);
	}

	public function render(string $subTemplate, array $params = []): void
	{
		$this->service->setSubActionContent($subTemplate);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}
}
